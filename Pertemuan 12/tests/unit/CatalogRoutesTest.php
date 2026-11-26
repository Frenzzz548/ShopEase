<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;
use Config\Services;

final class CatalogRoutesTest extends CIUnitTestCase
{
    public function testOperatorRoutesAllowOnlyTheAssignedCatalogOperations(): void
    {
        $routes = Services::routes(false)->loadRoutes();
        $expected = [
            'GET' => [
                'operator/categories',
                'operator/products',
                'operator/products/create',
                'operator/products/([0-9]+)',
                'operator/products/([0-9]+)/edit',
            ],
            'POST' => [
                'operator/products',
                'operator/products/([0-9]+)/update',
                'operator/products/([0-9]+)/images/([0-9]+)/primary',
            ],
        ];

        foreach ($expected as $method => $paths) {
            $operatorRoutes = array_filter(array_keys($routes->getRoutes($method)),
                static fn (string $path): bool => str_starts_with($path, 'operator/'));
            $this->assertEqualsCanonicalizing($paths, array_values($operatorRoutes));
            foreach ($paths as $path) {
                $this->assertSame(['auth:operator'], $routes->getFiltersForRoute($path, $method));
            }
        }
        $this->assertFalse($routes->shouldAutoRoute());
    }

    public function testCatalogDeletionsRemainAdminOnlyAndUseCsrf(): void
    {
        $routes = Services::routes(false)->loadRoutes();
        foreach (['admin/categories/([0-9]+)/delete', 'admin/products/([0-9]+)/delete',
            'admin/products/([0-9]+)/images/([0-9]+)/delete'] as $path) {
            $this->assertArrayNotHasKey($path, $routes->getRoutes('GET'));
            $this->assertSame(['auth:admin'], $routes->getFiltersForRoute($path, 'POST'));
        }
        $this->assertContains('csrf', config(Filters::class)->methods['POST']);
    }
}
