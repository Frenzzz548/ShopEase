<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Cookie;
use Config\Session;
use RuntimeException;

class AuthController extends BaseController
{
    // Keeps the password verification path consistent for unknown emails.
    private const DUMMY_HASH = '$2y$12$cc6imttlquAe6j5mL5A/KuzJC5Fjmiq68teQO3qzLitLo25j5MXfC';
    private const DUPLICATE_EMAIL = 'Email sudah terdaftar.';

    public function register(): string
    {
        return view('auth/register', [
            'title' => 'Daftar',
            'activePage' => 'register',
            'values' => [],
            'errors' => [],
        ]);
    }

    public function store(): ResponseInterface
    {
        $values = [
            'name' => trim($this->postString('name')),
            'email' => strtolower(trim($this->postString('email'))),
            'phone' => trim($this->postString('phone')),
            'address' => trim($this->postString('address')),
        ];
        $password = $this->postString('password');
        $data = $values + ['password' => $password, 'password_confirm' => $this->postString('password_confirm')];
        $rules = [
            'name' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]',
            'address' => 'permit_empty|max_length[16383]',
            'password' => 'required|min_length[' . UserModel::MIN_PASSWORD_LENGTH . ']',
            'password_confirm' => 'required|matches[password]',
        ];
        $messages = [
            'name' => ['required' => 'Nama wajib diisi.', 'max_length' => 'Nama maksimal 100 karakter.'],
            'email' => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.', 'max_length' => 'Email maksimal 100 karakter.'],
            'phone' => ['max_length' => 'Nomor telepon maksimal 20 karakter.'],
            'address' => ['max_length' => 'Alamat terlalu panjang.'],
            'password' => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 8 karakter.'],
            'password_confirm' => ['required' => 'Konfirmasi password wajib diisi.', 'matches' => 'Konfirmasi password tidak sama.'],
        ];

        $this->validateData($data, $rules, $messages);
        $errors = $this->validator->getErrors();
        if (! UserModel::canHashPassword($password)) {
            $errors['password'] = 'Password maksimal 72 byte dan harus menggunakan karakter teks.';
        }
        if ($errors !== []) {
            return $this->invalidRegistration($values, $errors);
        }

        $users = new UserModel();
        if ($users->findByEmail($values['email']) !== null) {
            return $this->invalidRegistration($values, ['email' => self::DUPLICATE_EMAIL]);
        }

        try {
            $userId = $users->insert([
                'name' => $values['name'],
                'email' => $values['email'],
                'phone' => $values['phone'] !== '' ? $values['phone'] : null,
                'address' => $values['address'] !== '' ? $values['address'] : null,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'customer',
            ]);
        } catch (DatabaseException $error) {
            if ($error->getCode() !== 1062) {
                throw $error;
            }

            return $this->invalidRegistration($values, ['email' => self::DUPLICATE_EMAIL]);
        }
        if ($userId === false) {
            if ($users->findByEmail($values['email']) !== null) {
                return $this->invalidRegistration($values, ['email' => self::DUPLICATE_EMAIL]);
            }

            throw new RuntimeException('Registrasi tidak dapat disimpan.');
        }

        return redirect()->to(url_to('login'), 303)->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function login(): string
    {
        return view('auth/login', [
            'title' => 'Login',
            'activePage' => 'login',
            'email' => '',
            'loginError' => null,
        ]);
    }

    public function authenticate(): ResponseInterface
    {
        $email = strtolower(trim($this->postString('email')));
        $password = $this->postString('password');
        $valid = $this->validateData(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|valid_email|max_length[100]', 'password' => 'required']
        );

        $passwordSupported = UserModel::canHashPassword($password);
        $user = $valid && $passwordSupported ? (new UserModel())->findByEmail($email) : null;
        $verified = password_verify($passwordSupported ? $password : '', $user['password'] ?? self::DUMMY_HASH);
        if (! $verified || $user === null || ! in_array($user['role'], UserModel::ROLES, true)) {
            return $this->response->setStatusCode(401)->setBody(view('auth/login', [
                'title' => 'Login',
                'activePage' => 'login',
                'email' => $email,
                'loginError' => 'Email atau password salah.',
            ]));
        }

        $session = session();
        $session->regenerate(true);
        $session->set([
            'isLoggedIn' => true,
            'user_id' => (int) $user['id_user'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

        $target = $user['role'] === 'customer' ? 'home' : $user['role'] . '-dashboard';

        return redirect()->to(url_to($target), 303);
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();
        $cookie = config(Cookie::class);

        return redirect()->to(url_to('login'), 303)
            ->deleteCookie(config(Session::class)->cookieName, $cookie->domain, $cookie->path);
    }

    private function postString(string $field): string
    {
        $value = $this->request->getPost($field);

        return is_string($value) ? $value : '';
    }

    private function invalidRegistration(array $values, array $errors): ResponseInterface
    {
        return $this->response->setStatusCode(422)->setBody(view('auth/register', [
            'title' => 'Daftar',
            'activePage' => 'register',
            'values' => $values,
            'errors' => $errors,
        ]));
    }
}
