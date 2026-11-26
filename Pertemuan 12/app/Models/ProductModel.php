<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class ProductModel extends Model
{
    public const STATUS_AVAILABLE = 'tersedia';
    public const STATUS_UNAVAILABLE = 'tidak_tersedia';
    public const STATUSES = [self::STATUS_AVAILABLE, self::STATUS_UNAVAILABLE];

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

    public function catalogCounts(): array
    {
        $available = $this->db->escape(self::STATUS_AVAILABLE);
        $unavailable = $this->db->escape(self::STATUS_UNAVAILABLE);
        $row = $this->db->table($this->table)
            ->selectCount('id_product', 'total')
            ->select("COUNT(CASE WHEN status = {$available} THEN 1 END) AS available", false)
            ->select("COUNT(CASE WHEN status = {$unavailable} THEN 1 END) AS unavailable", false)
            ->select('COUNT(CASE WHEN stock = 0 THEN 1 END) AS out_of_stock', false)
            ->get()
            ->getRowArray();

        return array_map('intval', $row);
    }

    public function findDashboardProducts(int $limit): array
    {
        return $this->withCategory()->orderBy('products.id_product', 'DESC')->findAll($limit);
    }

    public function findForCatalog(?int $categoryId = null, ?int $limit = null): array
    {
        $this->withCategory()->orderBy('products.id_product', 'ASC');
        if ($categoryId !== null) {
            $this->where('products.id_category', $categoryId);
        }
        return $this->withPrimaryImages($this->findAll($limit));
    }

    public function findForAdmin(string $search, ?int $categoryId, string $status, int $perPage, int $page): array
    {
        $this->withCategory()->orderBy('products.id_product', 'DESC');
        if ($search !== '') {
            $escape = $this->db->likeEscapeChar;
            $literal = strtr($search, [$escape => $escape . $escape, '%' => $escape . '%', '_' => $escape . '_']);
            $this->like('products.name', $literal);
        }
        if ($categoryId !== null) {
            $this->where('products.id_category', $categoryId);
        }
        if ($status !== '') {
            $this->where('products.status', $status);
        }

        return $this->withPrimaryImages($this->paginate($perPage, 'default', $page));
    }

    public function nextAvailableSlug(string $base): string
    {
        $slug = $base;
        for ($suffix = 2; $this->findBySlug($slug) !== null; $suffix++) {
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }

    public function lockForImageChange(int $id): bool
    {
        $table = $this->db->protectIdentifiers($this->table, true);
        $result = $this->db->query("SELECT id_product FROM {$table} WHERE id_product = ? FOR UPDATE", [$id]);
        if ($result === false) {
            throw new DatabaseException('Baris produk tidak dapat dikunci.');
        }

        return $result->getRowArray() !== null;
    }

    private function withPrimaryImages(array $products): array
    {
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
