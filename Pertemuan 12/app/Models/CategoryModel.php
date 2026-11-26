<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id_category';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['name', 'slug', 'description'];
    protected $useTimestamps = true;

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function listWithProductCounts(): array
    {
        return $this->select('categories.id_category, categories.name, categories.slug, categories.description')
            ->selectCount('products.id_product', 'product_count')
            ->join('products', 'products.id_category = categories.id_category', 'left')
            ->groupBy(['categories.id_category', 'categories.name', 'categories.slug', 'categories.description'])
            ->orderBy('categories.name', 'ASC')
            ->orderBy('categories.id_category', 'ASC')
            ->findAll();
    }

    public function hasDuplicate(string $name, string $slug, ?int $exceptId): bool
    {
        $query = $this->db->table($this->table)
            ->select('id_category')
            ->groupStart()
            ->where('name', $name)
            ->orWhere('slug', $slug)
            ->groupEnd();
        if ($exceptId !== null) {
            $query->where('id_category !=', $exceptId);
        }

        return $query->get(1)->getRowArray() !== null;
    }

    public function hasProducts(int $id): bool
    {
        return $this->db->table('products')->select('id_product')->where('id_category', $id)->get(1)->getRowArray() !== null;
    }
}
