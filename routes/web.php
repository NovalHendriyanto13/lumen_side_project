<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('register', 'AuthController@store');
    $router->get('me', 'AuthController@me');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->group(['prefix' => 'request-lists'], function () use ($router) {
            $router->get('/', 'RequestListController@index');      // Retrieve all request lists
            $router->get('/{id}', 'RequestListController@show');   // Retrieve a specific request list
            $router->post('/', 'RequestListController@store');     // Create a new request list
            $router->put('/update-status/{id}', 'RequestListController@updateStatus');
            $router->put('/{id}', 'RequestListController@update'); // Update a specific request list
            $router->delete('/{id}', 'RequestListController@destroy'); // Delete a specific request list
        });

        $router->group(['prefix' => 'request-details'], function () use ($router) {
            $router->get('/', 'RequestDetailController@index');      // Get all request details
            $router->get('/{id}', 'RequestDetailController@show');   // Get a specific request detail by ID
            $router->post('/', 'RequestDetailController@store');     // Create a new request detail
            $router->put('/{id}', 'RequestDetailController@update'); // Update an existing request detail
            $router->delete('/{id}', 'RequestDetailController@destroy'); // Delete a request detail by ID
        });
        
        $router->group(['prefix' => 'laundry-items'], function () use ($router) {
            $router->get('/', 'LaundryItemController@index');      // Get all laundry items
            $router->get('/{id}', 'LaundryItemController@show');   // Get a specific laundry item by ID
            $router->post('/', 'LaundryItemController@store');     // Create a new laundry item
            $router->put('/{id}', 'LaundryItemController@update'); // Update an existing laundry item
            $router->delete('/{id}', 'LaundryItemController@destroy'); // Delete a laundry item by ID
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', 'AuthController@index');
            $router->post('/', 'AuthController@store');
            $router->get('/profile', 'AuthController@profile');
            $router->put('/profile-update', 'AuthController@profileUpdate');
            $router->get('/{id}', 'AuthController@show');
            $router->put('/{id}', 'AuthController@update');
            $router->delete('/{id}', 'AuthController@destroy');
        });
        
    });
});

