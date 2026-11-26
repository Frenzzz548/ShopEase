<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class DashboardController extends BaseController
{
    public function admin(): string
    {
        return $this->catalogDashboard('admin');
    }

    public function operator(): string
    {
        return $this->catalogDashboard('operator');
    }

    public function index(): string
    {
        return view('auth/dashboard', [
            'title' => 'Dashboard ' . ucfirst(session()->get('role')),
            'activePage' => 'dashboard',
            'accountName' => session()->get('name'),
        ]);
    }

    private function catalogDashboard(string $role): string
    {
        $isAdmin = $role === 'admin';
        $roleLabel = $isAdmin ? 'Administrator' : 'Operator';
        $productModel = new ProductModel();
        $counts = $productModel->catalogCounts();
        $metrics = [
            'Total produk' => $counts['total'],
            'Produk tersedia' => $counts['available'],
            'Produk tidak tersedia' => $counts['unavailable'],
            'Produk dengan stok nol' => $counts['out_of_stock'],
        ];
        if ($isAdmin) {
            $metrics = ['Total kategori' => (new CategoryModel())->countAllResults()] + $metrics;
        }

        return view('dashboard/catalog', [
            'title' => 'Dashboard ' . $roleLabel,
            'roleLabel' => $roleLabel,
            'dashboardRoute' => $role . '-dashboard',
            'activeSection' => 'dashboard',
            'catalogRole' => $role,
            'isAdmin' => $isAdmin,
            'accountName' => session()->get('name'),
            'metrics' => $metrics,
            'products' => $productModel->findDashboardProducts(6),
        ]);
    }
}
