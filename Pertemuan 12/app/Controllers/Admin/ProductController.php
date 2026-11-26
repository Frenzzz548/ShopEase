<?php

namespace App\Controllers\Admin;

use App\Libraries\ProductImageManager;
use App\Libraries\ProductImageStorage;
use App\Models\CategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use DomainException;
use RuntimeException;

class ProductController extends CatalogController
{
    private const PER_PAGE = 12;
    private const MAX_ID = 2147483647;
    private const MAX_STOCK = 4294967295;
    private const CATEGORY_ERROR = 'Pilih kategori yang tersedia.';

    public function index(): string
    {
        $model = new ProductModel();
        $search = mb_substr($this->text($this->request->getGet('q')), 0, 150);
        $categoryId = $this->integer($this->text($this->request->getGet('category')), 1, self::MAX_ID);
        $status = $this->text($this->request->getGet('status'));
        $status = in_array($status, ProductModel::STATUSES, true) ? $status : '';
        $page = $this->integer($this->text($this->request->getGet('page')), 1, self::MAX_ID) ?? 1;
        $products = $model->findForAdmin($search, $categoryId, $status, self::PER_PAGE, $page);

        return $this->renderCatalogPage('products', 'index', 'Kelola Produk', [
            'categories' => $this->categories(),
            'products' => $products,
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
            'page' => $model->pager->getCurrentPage(),
            'pageCount' => $model->pager->getPageCount(),
            'perPage' => self::PER_PAGE,
        ]);
    }

    public function create(): string
    {
        return $this->renderForm();
    }

    public function store(): ResponseInterface
    {
        return $this->save();
    }

    public function show(string $id): string
    {
        $productId = $this->requiredId($id);
        $product = (new ProductModel())->findDetail($productId);
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return $this->renderCatalogPage('products', 'show', 'Detail Produk', ['product' => $product]);
    }

    public function edit(string $id): string
    {
        return $this->renderForm($this->findProduct(new ProductModel(), $id));
    }

    public function update(string $id): ResponseInterface
    {
        $model = new ProductModel();

        return $this->save($this->findProduct($model, $id));
    }

    public function delete(string $id): ResponseInterface
    {
        try {
            $cleaned = (new ProductImageManager())->deleteProduct($this->requiredId($id));
        } catch (DatabaseException $error) {
            log_message('error', 'Penghapusan produk gagal dengan kode database {code}.', ['code' => $error->getCode()]);

            return $this->backToList('error', 'Produk gagal dihapus. Periksa apakah produk masih dirujuk.');
        }

        return $cleaned
            ? $this->backToList('success', 'Produk berhasil dihapus.')
            : $this->backToList('error', 'Produk terhapus. Sebagian file upload belum bisa dibersihkan.');
    }

    public function makePrimary(string $id, string $image): ResponseInterface
    {
        return $this->changeImage($id, $image, false);
    }

    public function deleteImage(string $id, string $image): ResponseInterface
    {
        return $this->changeImage($id, $image, true);
    }

    private function changeImage(string $id, string $image, bool $delete): ResponseInterface
    {
        $productId = $this->requiredId($id);
        $imageId = $this->integer($image, 1, self::MAX_ID) ?? throw PageNotFoundException::forPageNotFound('Gambar produk tidak ditemukan.');
        $result = redirect()->to(url_to($this->catalogRole() . '-products-edit', $productId) . '#product-gallery', 303);
        try {
            $manager = new ProductImageManager();
            if ($delete) {
                return $manager->deleteImage($productId, $imageId)
                    ? $result->with('success', 'Gambar berhasil dihapus.')
                    : $result->with('error', 'Gambar dilepas dari produk. File upload belum bisa dibersihkan.');
            }

            $manager->makePrimary($productId, $imageId);

            return $result->with('success', 'Gambar utama diperbarui.');
        } catch (DatabaseException $error) {
            log_message('error', 'Perubahan gambar gagal dengan kode database {code}.', ['code' => $error->getCode()]);

            return $result->with('error', 'Gambar gagal diperbarui. Silakan coba lagi.');
        }
    }

    private function save(?array $product = null): ResponseInterface
    {
        $values = [];
        foreach (['name', 'id_category', 'description', 'price', 'stock', 'status'] as $key) {
            $values[$key] = $this->text($this->request->getPost($key));
        }
        $this->validateData($values, [
            'name' => 'required|max_length[150]',
            'description' => 'permit_empty|max_length[16383]',
            'status' => 'required|in_list[' . implode(',', ProductModel::STATUSES) . ']',
        ], [
            'name' => ['required' => 'Nama produk wajib diisi.', 'max_length' => 'Nama produk maksimal 150 karakter.'],
            'description' => ['max_length' => 'Deskripsi terlalu panjang.'],
            'status' => ['required' => 'Status wajib dipilih.', 'in_list' => 'Status produk tidak valid.'],
        ]);
        $errors = $this->validator->getErrors();
        $categoryId = $this->integer($values['id_category'], 1, self::MAX_ID);
        $price = $this->decimalPrice($values['price']);
        $stock = $this->integer($values['stock'], 0, self::MAX_STOCK);
        if ($categoryId === null || (new CategoryModel())->find($categoryId) === null) {
            $errors['id_category'] = self::CATEGORY_ERROR;
        }
        if ($price === null) {
            $errors['price'] = 'Harga harus dari 0 hingga 9.999.999.999,99 dengan maksimal dua angka desimal.';
        }
        if ($stock === null) {
            $errors['stock'] = 'Stok harus berupa bilangan bulat antara 0 dan 4.294.967.295.';
        }
        try {
            $uploads = $this->validatedUploads();
        } catch (DomainException $error) {
            $errors['images'] = $error->getMessage();
        }
        if ($errors !== []) {
            return $this->invalidForm($product, $values, $errors);
        }

        $data = [
            'name' => $values['name'],
            'id_category' => $categoryId,
            'description' => $values['description'] !== '' ? $values['description'] : null,
            'price' => $price,
            'stock' => $stock,
            'status' => $values['status'],
        ];
        try {
            $id = $product === null ? null : (int) $product['id_product'];
            (new ProductImageManager())->saveProduct($data, $id, $this->slugBase($values['name']), $uploads);
        } catch (DomainException $error) {
            return $this->invalidForm($product, $values, ['images' => $error->getMessage()]);
        } catch (PageNotFoundException $error) {
            throw $error;
        } catch (DatabaseException $error) {
            if ((int) $error->getCode() === 1452) {
                return $this->invalidForm($product, $values, ['id_category' => self::CATEGORY_ERROR]);
            }
            log_message('error', 'Penyimpanan produk gagal dengan kode database {code}.', ['code' => $error->getCode()]);

            return $this->invalidForm($product, $values, ['save' => 'Produk gagal disimpan. Silakan coba lagi.'], 500);
        } catch (RuntimeException $error) {
            log_message('error', 'Gagal menyimpan file gambar produk: {reason}', ['reason' => $error->getMessage()]);

            return $this->invalidForm($product, $values, ['images' => 'Gambar gagal disimpan. Silakan coba lagi.'], 500);
        }

        $message = $product === null ? 'Produk berhasil ditambahkan.' : 'Produk berhasil diperbarui.';
        if ($uploads !== []) {
            $message .= ' ' . count($uploads) . ' gambar disimpan.';
        }

        return $this->backToList('success', $message);
    }

    private function validatedUploads(): array
    {
        $files = $this->request->getFiles()['images'] ?? [];
        if (! is_array($files) || count($files) > ProductImageStorage::MAX_FILES) {
            throw new DomainException('Pilih maksimal ' . ProductImageStorage::MAX_FILES . ' gambar.');
        }

        $uploads = [];
        $validator = Services::validation(null, false);
        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile || ! is_int($key)) {
                throw new DomainException('Input upload gambar tidak valid.');
            }
            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($file->getError() === UPLOAD_ERR_INI_SIZE || $file->getError() === UPLOAD_ERR_FORM_SIZE) {
                throw new DomainException('Ukuran gambar maksimal 2 MB per file.');
            }
            $name = $file->getClientPath() ?? $file->getClientName();
            if (preg_match('~[/\\\\:\x00-\x1f\x7f]|\.(?:php[0-9]*|phtml|phar|svgz?)(?:\.|$)~i', $name)) {
                throw new DomainException('Nama atau path file gambar tidak diizinkan.');
            }
            $field = 'images.' . $key;
            $validator->reset()->setRules([$field => [
                'label' => 'Gambar produk',
                'rules' => [
                    "uploaded[{$field}]",
                    "max_size[{$field}," . ProductImageStorage::MAX_KIB . ']',
                    "ext_in[{$field},jpg,jpeg,png,webp]",
                    "mime_in[{$field},image/jpeg,image/png,image/webp]",
                    "is_image[{$field}]",
                    "max_dims[{$field}," . ProductImageStorage::MAX_DIMENSION . ',' . ProductImageStorage::MAX_DIMENSION . ']',
                ],
                'errors' => ['max_size' => 'Ukuran gambar maksimal 2 MB per file.'],
            ]]);
            if (! $validator->run()) {
                throw new DomainException($validator->getError($field));
            }
            $uploads[] = $file;
        }

        return $uploads;
    }

    private function slugBase(string $name): string
    {
        $slug = preg_replace('/[^a-z0-9-]+/', '-', mb_url_title($name, '-', true));
        $slug = rtrim(substr(trim($slug, '-'), 0, 168), '-');

        return $slug !== '' ? $slug : 'produk-' . substr(hash('sha256', mb_strtolower($name)), 0, 16);
    }

    private function decimalPrice(string $value): ?string
    {
        $value = str_replace(',', '.', $value);
        if (! preg_match('/\A(?:[0-9]+(?:\.[0-9]{1,2})?|\.[0-9]{1,2})\z/', $value)) {
            return null;
        }
        [$whole, $fraction] = explode('.', $value, 2) + [1 => ''];
        $whole = ltrim($whole, '0') ?: '0';

        return strlen($whole) <= 10 ? $whole . '.' . str_pad($fraction, 2, '0') : null;
    }

    private function integer(string $value, int $minimum, int $maximum): ?int
    {
        if (! ctype_digit($value)) {
            return null;
        }
        $parsed = filter_var(ltrim($value, '0') ?: '0', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $minimum, 'max_range' => $maximum],
        ]);

        return $parsed === false ? null : $parsed;
    }

    private function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function requiredId(string $id): int
    {
        return $this->integer($id, 1, self::MAX_ID) ?? throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
    }

    private function findProduct(ProductModel $model, string $id): array
    {
        return $model->find($this->requiredId($id)) ?? throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
    }

    private function categories(): array
    {
        return (new CategoryModel())->orderBy('name', 'ASC')->orderBy('id_category', 'ASC')->findAll();
    }

    private function backToList(string $type, string $message): ResponseInterface
    {
        return redirect()->to(url_to($this->catalogRole() . '-products'), 303)->with($type, $message);
    }

    private function invalidForm(?array $product, array $values, array $errors, int $status = 422): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setBody($this->renderForm($product, $values, $errors));
    }

    private function renderForm(?array $product = null, array $values = [], array $errors = []): string
    {
        return $this->renderCatalogPage('products', $product === null ? 'create' : 'edit', $product === null ? 'Tambah Produk' : 'Edit Produk', [
            'product' => $product,
            'categories' => $this->categories(),
            'values' => $values + ($product ?? []) + ['status' => ProductModel::STATUS_AVAILABLE, 'stock' => '0'],
            'errors' => $errors,
            'images' => $product === null ? [] : (new ProductImageModel())->forProduct((int) $product['id_product']),
        ]);
    }
}
