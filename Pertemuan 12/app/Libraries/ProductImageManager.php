<?php

namespace App\Libraries;

use App\Models\ProductImageModel;
use App\Models\ProductModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use DomainException;
use Throwable;

class ProductImageManager
{
    private BaseConnection $db;
    private ProductModel $products;
    private ProductImageModel $images;
    private ProductImageStorage $storage;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->products = new ProductModel($this->db);
        $this->images = new ProductImageModel($this->db);
        $this->storage = new ProductImageStorage();
    }

    public function saveProduct(array $data, ?int $id, string $slugBase, array $uploads): void
    {
        for ($attempt = 1; ; $attempt++) {
            $newPaths = [];
            try {
                $this->transaction(function () use ($data, $id, $slugBase, $uploads, &$newPaths): void {
                    if ($id === null) {
                        $slug = $this->products->nextAvailableSlug($slugBase);
                        $inserted = $this->products->insert(['slug' => $slug] + $data);
                        $this->requireSuccess($inserted !== false);
                        $id = (int) $inserted;
                    } else {
                        $this->lockProduct($id);
                        $this->requireSuccess($this->products->update($id, $data));
                    }

                    if ($uploads === []) {
                        return;
                    }
                    if (count($this->images->forProduct($id)) + count($uploads) > ProductImageStorage::MAX_FILES) {
                        throw new DomainException('Satu produk dapat memiliki maksimal ' . ProductImageStorage::MAX_FILES . ' gambar.');
                    }
                    foreach ($uploads as $upload) {
                        $path = $this->storage->store($upload);
                        $newPaths[] = $path;
                        $this->requireSuccess($this->images->insert(['id_product' => $id, 'image' => $path, 'is_primary' => 0]) !== false);
                    }
                    $this->ensurePrimary($id);
                });

                return;
            } catch (Throwable $error) {
                $this->cleanupPaths($newPaths);
                // Retry a concurrent slug collision in a fresh transaction.
                if ($id !== null || ! $error instanceof DatabaseException || (int) $error->getCode() !== 1062 || $attempt >= 3) {
                    throw $error;
                }
            }
        }
    }

    public function makePrimary(int $productId, int $imageId): void
    {
        $this->transaction(function () use ($productId, $imageId): void {
            $this->lockImage($productId, $imageId);
            $this->requireSuccess($this->images->makePrimary($productId, $imageId));
        });
    }

    public function deleteImage(int $productId, int $imageId): bool
    {
        $path = $this->transaction(function () use ($productId, $imageId): string {
            $image = $this->lockImage($productId, $imageId);
            $this->requireSuccess($this->images->delete($imageId));
            $this->ensurePrimary($productId);

            return $image['image'];
        });

        return $this->cleanupPaths([$path]);
    }

    public function deleteProduct(int $id): bool
    {
        $paths = $this->transaction(function () use ($id): array {
            $this->lockProduct($id);
            $paths = array_column($this->images->forProduct($id), 'image');
            $this->requireSuccess($this->products->delete($id));

            return $paths;
        });

        return $this->cleanupPaths($paths);
    }

    private function cleanupPaths(array $paths): bool
    {
        $cleaned = true;
        foreach (array_unique($paths) as $path) {
            if (! $this->storage->isUploadPath($path)) {
                continue;
            }
            try {
                if ($this->images->references($path) || $this->storage->remove($path)) {
                    continue;
                }
            } catch (Throwable) {
                // Keep the file when the database reference check fails.
            }
            log_message('error', 'Gagal membersihkan upload gambar {path}.', ['path' => $path]);
            $cleaned = false;
        }

        return $cleaned;
    }

    private function lockProduct(int $id): void
    {
        if (! $this->products->lockForImageChange($id)) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }
    }

    private function lockImage(int $productId, int $imageId): array
    {
        $this->lockProduct($productId);

        return $this->images->where('id_product', $productId)->find($imageId)
            ?? throw PageNotFoundException::forPageNotFound('Gambar produk tidak ditemukan.');
    }

    private function ensurePrimary(int $id): void
    {
        $images = $this->images->forProduct($id);
        if ($images === []) {
            return;
        }
        $primary = array_filter($images, static fn (array $image): bool => (int) $image['is_primary'] === 1);
        if (count($primary) !== 1) {
            $first = reset($primary) ?: $images[0];
            $this->requireSuccess($this->images->makePrimary($id, (int) $first['id_image']));
        }
    }

    private function transaction(callable $operation): mixed
    {
        $this->requireSuccess($this->db->transBegin());
        try {
            $result = $operation();
            $this->requireSuccess($this->db->transStatus() && $this->db->transCommit());

            return $result;
        } catch (Throwable $error) {
            if ($this->db->transRollback()) {
                $this->db->resetTransStatus();
            }
            throw $error;
        }
    }

    private function requireSuccess(bool $success): void
    {
        if (! $success) {
            throw new DatabaseException('Perubahan produk tidak dapat disimpan.', (int) $this->db->error()['code']);
        }
    }
}
