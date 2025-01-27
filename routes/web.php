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
    $router->get('/dropdown/status', 'ComplaintController@dropdownStatus');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->group(['prefix' => 'complaint'], function () use ($router) {
            $router->get('/', 'ComplaintController@index');      // Retrieve all request lists
            $router->get('/{id}', 'ComplaintController@show');  
            
            $router->group(['middleware' => 'role_access'], function() use ($router) {
                $router->delete('/{id}', 'ComplaintController@destroy');
                // $router->post('/download-report', 'ComplaintController@downloadReport');
                $router->post('/download-pdf', 'ComplaintController@downloadPdf');
                $router->put('/update-status/{id}', 'ComplaintController@updateStatus');
            });
            $router->group(['middleware' => 'role_access:user'], function() use ($router) {
                $router->post('/', 'ComplaintController@store');
                $router->post('/{id}', 'ComplaintController@update'); // Update a specific request list
            });
        });

        $router->group(['prefix' => 'respond'], function () use ($router) {
            $router->post('/', 'RespondController@store');     // Create a new request detail
            $router->post('/{id}', 'RespondController@update'); // Update an existing request detail
            $router->delete('/{id}', 'RespondController@destroy'); // Delete a request detail by ID
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

