<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Preflight handler untuk semua OPTIONS request
$routes->options('(:any)', function() {
    $response = service('response');
    $response->setHeader('Access-Control-Allow-Origin', '*');
    $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH');
    return $response->setStatusCode(200);
});

// Auth routes - PASTIKAN ADA INI
$routes->post('api/auth/register', 'Auth::register');
$routes->post('api/auth/login', 'Auth::login');
$routes->get('api/auth/me', 'Auth::me');
$routes->get('api/test/users', 'Test::users');

// Books routes
$routes->get('api/books', 'Books::index');
$routes->get('api/books/(:num)', 'Books::show');
$routes->post('api/books', 'Books::create');
$routes->put('api/books/(:num)', 'Books::update');
$routes->delete('api/books/(:num)', 'Books::delete');

// Peminjaman routes
$routes->get('api/peminjaman', 'Peminjaman::index');
$routes->get('api/peminjaman/(:num)', 'Peminjaman::show/$1');
$routes->post('api/peminjaman', 'Peminjaman::create');
$routes->put('api/peminjaman/(:num)', 'Peminjaman::update/$1');
        
// Test route
$routes->get('api/test', 'Test::index');

// Home route
$routes->get('/', function() {
    return service('response')->setJSON([
        'status' => true,
        'message' => 'API Perpustakaan CI4 Ready!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});