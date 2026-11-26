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

    public function forProduct(int $productId): array
    {
        return $this->where('id_product', $productId)->orderBy('id_image', 'ASC')->findAll();
    }

    public function makePrimary(int $productId, int $imageId): bool
    {
        return $this->where('id_product', $productId)->where('is_primary', 1)->set('is_primary', 0)->update()
            && $this->where('id_product', $productId)->update($imageId, ['is_primary' => 1]);
    }

    public function references(string $path): bool
    {
        return $this->where('image', $path)->countAllResults() > 0;
    }

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
