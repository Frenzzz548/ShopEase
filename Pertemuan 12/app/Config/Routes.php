<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'HomeController::index', ['as' => 'home']);
$routes->get('products', 'ProductController::index', ['as' => 'products']);
$routes->get('products/(:num)', 'ProductController::detail/$1', ['as' => 'product-detail']);
$routes->get('product-images/(:segment)', 'ProductImageController::show/$1', ['as' => 'product-image']);
$routes->get('about', 'HomeController::about', ['as' => 'about']);

$routes->get('register', 'AuthController::register', ['as' => 'register']);
$routes->post('register', 'AuthController::store');
$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('login', 'AuthController::authenticate');
$routes->post('logout', 'AuthController::logout', ['as' => 'logout', 'filter' => 'auth']);

$routes->get('admin', 'DashboardController::admin', ['as' => 'admin-dashboard', 'filter' => 'auth:admin']);
$routes->get('operator', 'DashboardController::operator', ['as' => 'operator-dashboard', 'filter' => 'auth:operator']);
$routes->get('customer', 'DashboardController::index', ['as' => 'customer-dashboard', 'filter' => 'auth:customer']);

$catalogRoutes = static function (RouteCollection $routes, string $role): void {
    $routes->get('categories', 'CategoryController::index', ['as' => $role . '-categories']);
    $routes->get('products', 'ProductController::index', ['as' => $role . '-products']);
    $routes->get('products/create', 'ProductController::create', ['as' => $role . '-products-create']);
    $routes->post('products', 'ProductController::store', ['as' => $role . '-products-store']);
    $routes->get('products/(:num)', 'ProductController::show/$1', ['as' => $role . '-products-show']);
    $routes->get('products/(:num)/edit', 'ProductController::edit/$1', ['as' => $role . '-products-edit']);
    $routes->post('products/(:num)/update', 'ProductController::update/$1', ['as' => $role . '-products-update']);
    $routes->post('products/(:num)/images/(:num)/primary', 'ProductController::makePrimary/$1/$2', ['as' => $role . '-product-image-primary']);
};

$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth:admin'], static function (RouteCollection $routes) use ($catalogRoutes): void {
    $catalogRoutes($routes, 'admin');
    $routes->get('categories/create', 'CategoryController::create', ['as' => 'admin-categories-create']);
    $routes->post('categories', 'CategoryController::store', ['as' => 'admin-categories-store']);
    $routes->get('categories/(:num)/edit', 'CategoryController::edit/$1', ['as' => 'admin-categories-edit']);
    $routes->post('categories/(:num)/update', 'CategoryController::update/$1', ['as' => 'admin-categories-update']);
    $routes->post('categories/(:num)/delete', 'CategoryController::delete/$1', ['as' => 'admin-categories-delete']);
    $routes->post('products/(:num)/delete', 'ProductController::delete/$1', ['as' => 'admin-products-delete']);
    $routes->post('products/(:num)/images/(:num)/delete', 'ProductController::deleteImage/$1/$2', ['as' => 'admin-product-image-delete']);
});

$routes->group('operator', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth:operator'], static function (RouteCollection $routes) use ($catalogRoutes): void {
    $catalogRoutes($routes, 'operator');
});
