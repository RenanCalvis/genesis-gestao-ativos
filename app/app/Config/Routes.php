<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Patrimonios
$routes->group('assets', static function ($routes): void {
    $routes->get('/', 'AssetController::index');
    $routes->post('decommission/(:segment)', 'AssetController::decommission/$1');
});


// Emprestimos
$routes->group('loans', static function ($routes): void {
    $routes->get('/', 'LoanController::index');
    $routes->post('/', 'LoanController::createLoan');
});


// Estabelecimentos 
$routes->group('establishments', static function ($routes): void {
    $routes->get('/', 'EstablishmentController::index');
});
