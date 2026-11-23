<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table = 'product_images';
    protected $primaryKey = 'id_image';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['id_product', 'image', 'is_primary'];
    protected $useTimestamps = true;
    protected $updatedField = '';

    public function findPrimaryForProduct(int $productId): ?array
    {
        return $this->where('id_product', $productId)
            ->where('is_primary', 1)
            ->orderBy('id_image', 'ASC')
            ->first();
    }

    public function findPrimaryByProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $images = $this->whereIn('id_product', $productIds)
            ->where('is_primary', 1)
            ->orderBy('id_image', 'ASC')
            ->findAll();

        $byProduct = [];
        foreach ($images as $image) {
            $byProduct[$image['id_product']] ??= $image;
        }

        return $byProduct;
    }
}
