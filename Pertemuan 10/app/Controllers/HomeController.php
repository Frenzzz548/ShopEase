<?php

namespace App\Controllers;

use Config\Catalog;

class HomeController extends BaseController
{
    public function index(): string
    {
        $catalog = config(Catalog::class);

        return view('frontend/home', [
            'title' => 'Beranda',
            'activePage' => 'home',
            'categories' => $catalog->categories,
            'products' => $catalog->products,
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
