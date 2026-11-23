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
}
