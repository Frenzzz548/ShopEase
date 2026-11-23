<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Catalog extends BaseConfig
{
    public array $categories = [
        'fashion' => ['name' => 'Fashion', 'cover' => 2],
        'elektronik' => ['name' => 'Elektronik', 'cover' => 1],
        'olahraga' => ['name' => 'Olahraga', 'cover' => 5],
        'aksesoris' => ['name' => 'Aksesoris', 'cover' => 4],
    ];

    // The array keys are the IDs used in product detail URLs.
    public array $products = [
        1 => [
            'name' => 'Wireless Headphone',
            'category' => 'elektronik',
            'price' => 299000,
            'description' => 'Headphone Bluetooth dengan bantalan lembut untuk mendengarkan musik dan podcast.',
            'stock' => 20,
            'image' => 'wireless-headphone.webp',
        ],
        2 => [
            'name' => 'Sneakers Casual',
            'category' => 'fashion',
            'price' => 350000,
            'description' => 'Sepatu kasual berwarna putih dan navy dengan sol karet untuk aktivitas sehari-hari.',
            'stock' => 15,
            'image' => 'sneakers-casual.webp',
        ],
        3 => [
            'name' => 'Smart Watch',
            'category' => 'aksesoris',
            'price' => 450000,
            'description' => 'Jam tangan digital dengan tali silikon dan tampilan yang mudah dibaca.',
            'stock' => 10,
            'image' => 'smart-watch.webp',
        ],
        4 => [
            'name' => 'Tas Ransel Harian',
            'category' => 'aksesoris',
            'price' => 249000,
            'description' => 'Ransel kanvas navy dengan saku depan beritsleting untuk perlengkapan kuliah atau kerja.',
            'stock' => 8,
            'image' => 'tas-ransel-harian.webp',
        ],
        5 => [
            'name' => 'Botol Minum Sport',
            'category' => 'olahraga',
            'price' => 129000,
            'description' => 'Botol minum baja tahan karat berkapasitas 600 ml dengan pegangan pada tutup.',
            'stock' => 12,
            'image' => 'botol-minum-sport.webp',
        ],
        6 => [
            'name' => 'Matras Yoga',
            'category' => 'olahraga',
            'price' => 189000,
            'description' => 'Matras yoga bertekstur dengan ketebalan 6 mm yang mudah digulung setelah latihan.',
            'stock' => 0,
            'image' => 'matras-yoga.webp',
        ],
    ];
}
