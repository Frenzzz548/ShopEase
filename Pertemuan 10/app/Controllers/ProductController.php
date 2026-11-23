<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Catalog;

class ProductController extends BaseController
{
    public function index(): string
    {
        $catalog = config(Catalog::class);
        $category = $this->request->getGet('category');
        $selectedCategory = is_string($category) && isset($catalog->categories[$category]) ? $category : null;
        $products = $catalog->products;

        if ($selectedCategory !== null) {
            $products = array_filter($products, static fn (array $product): bool => $product['category'] === $selectedCategory);
        }

        return view('frontend/products', [
            'title' => 'Katalog Produk',
            'activePage' => 'products',
            'categories' => $catalog->categories,
            'selectedCategory' => $selectedCategory,
            'products' => $products,
        ]);
    }

    public function detail(string $id): string
    {
        $catalog = config(Catalog::class);
        $product = $catalog->products[ltrim($id, '0')] ?? null;

        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return view('frontend/product_detail', [
            'title' => $product['name'],
            'activePage' => 'products',
            'navigationCurrent' => 'location',
            'categoryName' => $catalog->categories[$product['category']]['name'],
            'product' => $product,
        ]);
    }
}
