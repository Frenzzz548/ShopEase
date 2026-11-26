<?php

namespace App\Controllers;

use App\Libraries\ProductImageStorage;
use App\Models\ProductImageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class ProductImageController extends BaseController
{
    public function show(string $name): ResponseInterface
    {
        $path = ProductImageStorage::PREFIX . $name;
        $storage = new ProductImageStorage();
        $file = $storage->file($path);
        if ($file === null || ! (new ProductImageModel())->references($path)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $bytes = @file_get_contents($file);
        if ($bytes === false) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->setContentType($storage->mime($path), '')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'no-cache')
            ->setBody($bytes);
    }
}
