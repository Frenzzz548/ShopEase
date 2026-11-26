<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();
        if ($session->get('isLoggedIn') !== true || ! $session->get('user_id')) {
            return redirect()->to(url_to('login'));
        }

        $role = $session->get('role');
        if (! in_array($role, UserModel::ROLES, true) || ($arguments && ! in_array($role, $arguments, true))) {
            return service('response')->setStatusCode(403)->setContentType('text/plain')->setBody('Akses ditolak.');
        }

        $account = (new UserModel())->select('role')->find($session->get('user_id'));
        if (($account['role'] ?? null) !== $role) {
            $session->destroy();

            return redirect()->to(url_to('login'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
