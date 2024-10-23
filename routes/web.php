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
    $router->get('image', 'Controller@getImage');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->group(['prefix' => 'pengaduan'], function () use ($router) {
            $router->get('/', 'PengaduanController@index');      // Retrieve all request lists
            $router->get('/{id}', 'PengaduanController@show');  
            $router->group(['middleware' => 'role_access'], function() use ($router) {
                $router->get('/dropdown/status', 'PengaduanController@dropdownStatus');
                $router->delete('/{id}', 'PengaduanController@destroy');
                // $router->post('/download-report', 'PengaduanController@downloadReport');
                $router->post('/download-pdf', 'PengaduanController@downloadPdf');
            });
            $router->group(['middleware' => 'role_access:user'], function() use ($router) {
                $router->post('/', 'PengaduanController@store');
                $router->post('/{id}', 'PengaduanController@update'); // Update a specific request list
            });
        });

        $router->group(['prefix' => 'request-details'], function () use ($router) {
            $router->post('/', 'RequestDetailController@store');     // Create a new request detail
            $router->post('/{id}', 'RequestDetailController@update'); // Update an existing request detail
            $router->delete('/{id}', 'RequestDetailController@destroy'); // Delete a request detail by ID
        });
        
        $router->group(['prefix' => 'laundry-items'], function () use ($router) {
            $router->get('/', 'LaundryItemController@index');      // Get all laundry items
            $router->group(['middleware' => 'role_access'], function() use ($router) {
                $router->get('/{id}', 'LaundryItemController@show');   // Get a specific laundry item by ID
                $router->post('/', 'LaundryItemController@store');     // Create a new laundry item
                $router->put('/{id}', 'LaundryItemController@update'); // Update an existing laundry item
                $router->delete('/{id}', 'LaundryItemController@destroy'); // Delete a laundry item by ID
            });
        });

        $router->group(['prefix' => 'maskapai'], function () use ($router) {
            $router->get('/', 'MaskapaiController@index');      // Get all laundry items
            $router->get('/{id}', 'MaskapaiController@show');   // Get a specific laundry item by ID
            $router->post('/', 'MaskapaiController@store');     // Create a new laundry item
            $router->put('/{id}', 'MaskapaiController@update'); // Update an existing laundry item
            $router->delete('/{id}', 'MaskapaiController@destroy'); // Delete a laundry item by ID
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', 'AuthController@index');
            $router->post('/', 'AuthController@store');
            $router->get('/profile', 'AuthController@profile');
            $router->put('/profile-update', 'AuthController@profileUpdate');
            $router->put('/change-password', 'AuthController@changePassword');
            $router->get('/{id}', 'AuthController@show');
            $router->put('/{id}', 'AuthController@update');
            $router->delete('/{id}', 'AuthController@destroy');
        });
        
    });
});

