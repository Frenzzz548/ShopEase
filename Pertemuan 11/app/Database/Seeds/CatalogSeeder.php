<?php

namespace App\Database\Seeds;

use App\Models\CategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use CodeIgniter\Database\Seeder;
use Config\Catalog;
use RuntimeException;
use Throwable;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = config(Catalog::class);
        $categoryModel = new CategoryModel($this->db);
        $productModel = new ProductModel($this->db);
        $imageModel = new ProductImageModel($this->db);

        if (! $this->db->transBegin()) {
            throw new RuntimeException('Transaksi seeder katalog gagal dimulai.');
        }

        try {
            $categoryIds = [];
            foreach ($catalog->categories as $slug => $category) {
                $existing = $categoryModel->findBySlug($slug);
                $id = $existing['id_category'] ?? $categoryModel->insert([
                    'name' => $category['name'],
                    'slug' => $slug,
                ]);
                if ($id === false) {
                    throw new RuntimeException('Kategori gagal disimpan: ' . $slug);
                }
                $categoryIds[$slug] = $id;
            }

            foreach ($catalog->products as $product) {
                $slug = url_title($product['name'], '-', true);
                if ($productModel->findBySlug($slug) !== null) {
                    continue;
                }

                $productId = $productModel->insert([
                    'id_category' => $categoryIds[$product['category']],
                    'name'        => $product['name'],
                    'slug'        => $slug,
                    'description' => $product['description'],
                    'price'       => $product['price'],
                    'stock'       => $product['stock'],
                    'status'      => $product['stock'] > 0 ? ProductModel::STATUS_AVAILABLE : ProductModel::STATUS_UNAVAILABLE,
                ]);
                if ($productId === false) {
                    throw new RuntimeException('Produk gagal disimpan: ' . $slug);
                }

                $image = 'images/shopease/' . $product['image'];
                if (! is_file(FCPATH . $image) || $imageModel->insert([
                    'id_product' => $productId,
                    'image'      => $image,
                    'is_primary' => 1,
                ]) === false) {
                    throw new RuntimeException('Gambar utama gagal disimpan: ' . $slug);
                }
            }

            if (! $this->db->transStatus() || ! $this->db->transCommit()) {
                throw new RuntimeException('Transaksi seeder katalog gagal disimpan.');
            }
        } catch (Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}
