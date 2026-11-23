<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'HomeController::index', ['as' => 'home']);
$routes->get('products', 'ProductController::index', ['as' => 'products']);
$routes->get('products/(:num)', 'ProductController::detail/$1', ['as' => 'product-detail']);
$routes->get('about', 'HomeController::about', ['as' => 'about']);
