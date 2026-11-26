<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default homepage route.
$routes->get('/', 'Home::index');

// Register Shield's built-in authentication routes.
service('auth')->routes($routes);

// Group all JobTrack API endpoints under /api.
$routes->group('api', static function ($routes) {

    // Public routes: users don't need an access token.
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login', 'Api\AuthController::login');

    // Protected authentication route: requires a valid access token.
    $routes->post('logout', 'Api\AuthController::logout', [
        'filter' => 'tokens',
    ]);

    // Protected application routes: all require a valid access token.
    $routes->group('applications', ['filter' => 'tokens'], static function ($routes) {

        // Retrieve all applications belonging to the authenticated user.
        $routes->get('/', 'Api\JobApplicationsController::index');

        // Create a job application.
        $routes->post('/', 'Api\JobApplicationsController::create');

        // Retrieve one job application.
        $routes->get('(:num)', 'Api\JobApplicationsController::show/$1');

        // Update one job application.
        $routes->put('(:num)', 'Api\JobApplicationsController::update/$1');

        // Delete one job application.
        $routes->delete('(:num)', 'Api\JobApplicationsController::delete/$1');

        // Retrieve the status history of one application.
        $routes->get('(:num)/history', 'Api\JobApplicationsController::history/$1');

        // Retrieve interviews belonging to an application.
        $routes->get('(:num)/interviews', 'Api\InterviewsController::index/$1');

        // Schedule an interview for an application.
        $routes->post('(:num)/interviews', 'Api\InterviewsController::create/$1');
    });

    // Protected routes for individual interviews.
    $routes->group('interviews', ['filter' => 'tokens'], static function ($routes) {

    // Retrieve one interview.
    $routes->get('(:num)', 'Api\InterviewsController::show/$1');

    // Update an interview.
    $routes->put('(:num)', 'Api\InterviewsController::update/$1');

    // Delete an interview.
    $routes->delete('(:num)', 'Api\InterviewsController::delete/$1');
    });
});



