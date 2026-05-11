<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AssetController::index');

// Patrimonios
$routes->group('assets', static function ($routes): void {
    $routes->get('/', 'AssetController::index');
    $routes->post('/', 'AssetController::create');
    $routes->post('update/(:segment)', 'AssetController::update/$1');
    $routes->post('decommission/(:segment)', 'AssetController::decommission/$1');
});


// Emprestimos
$routes->group('loans', static function ($routes): void {
    $routes->get('/', 'LoanController::index');
    $routes->post('/', 'LoanController::createLoan');
    $routes->post('return/(:segment)', 'LoanController::returnLoan/$1');
});


// Estabelecimentos 
$routes->group('establishments', static function ($routes): void {
    $routes->get('/', 'EstablishmentController::index');
    $routes->post('/', 'EstablishmentController::create');
    $routes->post('update/(:segment)', 'EstablishmentController::update/$1');
});
