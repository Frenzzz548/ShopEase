<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class CatalogController extends BaseController
{
    protected function catalogRole(): string
    {
        return session()->get('role');
    }

    protected function renderCatalogPage(string $section, string $page, string $title, array $data): string
    {
        $role = $this->catalogRole();
        $isAdmin = $role === 'admin';

        return view('admin/' . $section . '/' . $page, $data + [
            'title' => $title,
            'roleLabel' => $isAdmin ? 'Administrator' : 'Operator',
            'dashboardRoute' => $role . '-dashboard',
            'activeSection' => $section,
            'catalogRole' => $role,
            'isAdmin' => $isAdmin,
            'accountName' => session()->get('name'),
        ]);
    }
}
