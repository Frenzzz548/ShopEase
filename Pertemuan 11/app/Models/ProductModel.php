<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    public const STATUS_AVAILABLE = 'tersedia';
    public const STATUS_UNAVAILABLE = 'tidak_tersedia';

    protected $table = 'products';
    protected $primaryKey = 'id_product';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['id_category', 'name', 'slug', 'description', 'price', 'stock', 'status'];
    protected $useTimestamps = true;

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function findAvailable(): array
    {
        return $this->where('status', self::STATUS_AVAILABLE)
            ->where('stock >', 0)
            ->orderBy('id_product', 'ASC')
            ->findAll();
    }

    public function findByCategory(int $categoryId): array
    {
        return $this->where('id_category', $categoryId)
            ->orderBy('id_product', 'ASC')
            ->findAll();
    }

    public function findForCatalog(?int $categoryId = null, ?int $limit = null): array
    {
        $this->withCategory()->orderBy('products.id_product', 'ASC');
        if ($categoryId !== null) {
            $this->where('products.id_category', $categoryId);
        }
        $products = $this->findAll($limit);

        $imageModel = new ProductImageModel($this->db);
        $images = $imageModel->findPrimaryByProductIds(array_column($products, 'id_product'));
        foreach ($products as $index => $product) {
            $products[$index]['primary_image'] = $images[$product['id_product']] ?? null;
        }

        return $products;
    }

    public function findDetail(int $productId): ?array
    {
        $product = $this->withCategory()->find($productId);

        if ($product === null) {
            return null;
        }

        $imageModel = new ProductImageModel($this->db);
        $product['primary_image'] = $imageModel->findPrimaryForProduct($productId);

        return $product;
    }

    private function withCategory(): self
    {
        return $this->select('products.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id_category = products.id_category');
    }
}
