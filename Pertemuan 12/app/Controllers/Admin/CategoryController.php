<?php

namespace App\Controllers\Admin;

use App\Models\CategoryModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class CategoryController extends CatalogController
{
    private const DUPLICATE = 'Nama kategori atau slug sudah digunakan.';
    private const IN_USE = 'Kategori masih memiliki produk dan tidak dapat dihapus.';

    public function index(): string
    {
        return $this->renderCatalogPage('categories', 'index', 'Kelola Kategori', [
            'categories' => (new CategoryModel())->listWithProductCounts(),
        ]);
    }

    public function create(): string
    {
        return $this->renderForm();
    }

    public function store(): ResponseInterface
    {
        return $this->save(new CategoryModel());
    }

    public function edit(string $id): string
    {
        return $this->renderForm($this->findCategory(new CategoryModel(), $id));
    }

    public function update(string $id): ResponseInterface
    {
        $model = new CategoryModel();

        return $this->save($model, $this->findCategory($model, $id));
    }

    public function delete(string $id): ResponseInterface
    {
        $model = new CategoryModel();
        $categoryId = (int) $this->findCategory($model, $id)['id_category'];
        if ($model->hasProducts($categoryId)) {
            return $this->backToList('error', self::IN_USE);
        }

        try {
            $deleted = $model->delete($categoryId);
        } catch (DatabaseException $error) {
            if ((int) $error->getCode() === 1451) {
                return $this->backToList('error', self::IN_USE);
            }
            log_message('error', 'Penghapusan kategori gagal dengan kode database {code}.', ['code' => $error->getCode()]);

            return $this->backToList('error', 'Kategori gagal dihapus. Silakan coba lagi.');
        }

        if (! $deleted) {
            return $this->backToList('error', $model->hasProducts($categoryId) ? self::IN_USE : 'Kategori gagal dihapus. Silakan coba lagi.');
        }

        return $this->backToList('success', 'Kategori berhasil dihapus.');
    }

    private function save(CategoryModel $model, ?array $category = null): ResponseInterface
    {
        $name = $this->request->getPost('name');
        $description = $this->request->getPost('description');
        $values = [
            'name' => is_string($name) ? trim($name) : '',
            'description' => is_string($description) ? trim($description) : '',
        ];
        if (! $this->validateData($values, [
            'name' => 'required|max_length[100]',
            'description' => 'permit_empty|max_length[16383]',
        ], [
            'name' => ['required' => 'Nama kategori wajib diisi.', 'max_length' => 'Nama kategori maksimal 100 karakter.'],
            'description' => ['max_length' => 'Deskripsi terlalu panjang.'],
        ])) {
            return $this->invalidForm($category, $values, $this->validator->getErrors());
        }

        $categoryId = $category === null ? null : (int) $category['id_category'];
        $slug = $values['name'] === ($category['name'] ?? null)
            ? $category['slug']
            : $this->slugFromName($values['name']);
        if ($model->hasDuplicate($values['name'], $slug, $categoryId)) {
            return $this->invalidForm($category, $values, [self::DUPLICATE]);
        }

        $data = [
            'name' => $values['name'],
            'slug' => $slug,
            'description' => $values['description'] !== '' ? $values['description'] : null,
        ];
        try {
            $saved = $categoryId === null ? $model->insert($data) : $model->update($categoryId, $data);
        } catch (DatabaseException $error) {
            if ((int) $error->getCode() === 1062) {
                return $this->invalidForm($category, $values, [self::DUPLICATE]);
            }
            log_message('error', 'Penyimpanan kategori gagal dengan kode database {code}.', ['code' => $error->getCode()]);

            return $this->backToList('error', 'Kategori gagal disimpan. Silakan coba lagi.');
        }
        if ($saved === false) {
            if ($model->hasDuplicate($values['name'], $slug, $categoryId)) {
                return $this->invalidForm($category, $values, [self::DUPLICATE]);
            }

            return $this->backToList('error', 'Kategori gagal disimpan. Silakan coba lagi.');
        }

        return $this->backToList('success', $categoryId === null ? 'Kategori berhasil ditambahkan.' : 'Kategori berhasil diperbarui.');
    }

    private function slugFromName(string $name): string
    {
        $slug = preg_replace('/[^a-z0-9-]+/', '-', mb_url_title($name, '-', true));
        $slug = rtrim(substr(trim($slug, '-'), 0, 120), '-');

        return $slug !== '' ? $slug : 'kategori-' . substr(hash('sha256', mb_strtolower($name)), 0, 16);
    }

    private function findCategory(CategoryModel $model, string $id): array
    {
        $categoryId = filter_var(ltrim($id, '0'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        $category = $categoryId === false ? null : $model->find($categoryId);
        if ($category === null) {
            throw PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        return $category;
    }

    private function backToList(string $type, string $message): ResponseInterface
    {
        return redirect()->to(url_to('admin-categories'), 303)->with($type, $message);
    }

    private function invalidForm(?array $category, array $values, array $errors): ResponseInterface
    {
        return $this->response->setStatusCode(422)->setBody($this->renderForm($category, $values, $errors));
    }

    private function renderForm(?array $category = null, array $values = [], array $errors = []): string
    {
        return $this->renderCatalogPage('categories', $category === null ? 'create' : 'edit', $category === null ? 'Tambah Kategori' : 'Edit Kategori', [
            'category' => $category,
            'values' => $values + ($category ?? []),
            'errors' => $errors,
        ]);
    }
}
