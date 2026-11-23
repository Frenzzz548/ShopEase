<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $categories = (new CategoryModel())->orderBy('id_category', 'ASC')->findAll();
        $products = (new ProductModel())->findForCatalog(limit: 6);

        $categoryImages = [];
        foreach ($products as $product) {
            $categoryImages[$product['id_category']] ??= catalog_image_url($product['primary_image']['image'] ?? null);
        }

        return view('frontend/home', [
            'title' => 'Beranda',
            'activePage' => 'home',
            'categories' => $categories,
            'categoryImages' => $categoryImages,
            'products' => $products,
            'emptyProductsMessage' => 'Belum ada produk di katalog.',
        ]);
    }

    public function about(): string
    {
        return view('frontend/about', [
            'title' => 'Tentang ShopEase',
            'activePage' => 'about',
        ]);
    }
}
