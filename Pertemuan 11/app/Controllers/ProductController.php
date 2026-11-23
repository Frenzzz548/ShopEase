<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProductController extends BaseController
{
    public function index(): string
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->orderBy('id_category', 'ASC')->findAll();
        $slug = $this->request->getGet('category');
        $hasFilter = $slug !== null && $slug !== '';
        $category = $hasFilter && is_string($slug) && strlen($slug) <= 120
            ? $categoryModel->findBySlug($slug)
            : null;
        $categoryNotFound = $hasFilter && $category === null;

        $categoryId = $category === null ? null : (int) $category['id_category'];
        $products = $categoryNotFound ? [] : (new ProductModel())->findForCatalog($categoryId);

        return view('frontend/products', [
            'title' => 'Katalog Produk',
            'activePage' => 'products',
            'categories' => $categories,
            'selectedCategory' => $category['slug'] ?? null,
            'categoryNotFound' => $categoryNotFound,
            'emptyProductsMessage' => match (true) {
                $categoryNotFound => 'Kategori tidak ditemukan.',
                $category !== null => 'Belum ada produk di kategori ini.',
                default => 'Belum ada produk di katalog.',
            },
            'products' => $products,
        ]);
    }

    public function detail(string $id): string
    {
        $productId = filter_var(ltrim($id, '0'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
        $product = $productId === false ? null : (new ProductModel())->findDetail($productId);

        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        return view('frontend/product_detail', [
            'title' => $product['name'],
            'activePage' => 'products',
            'navigationCurrent' => 'location',
            'product' => $product,
        ]);
    }
}
