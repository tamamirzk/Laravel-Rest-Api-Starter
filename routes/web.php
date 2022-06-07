<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Str;

$router->get('/', function () { return response()->json( [ 'code' => 404, 'status' => 'HTTP 404', ], 404 ); });
$router->get('/test', 'HomeController@index');

$router->group(['prefix' => 'authentication', ], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->get('/verify/{id}', 'AuthController@verify');
    $router->post('/login/google', 'AuthController@loginGoogle');
    $router->post('/register/google', 'AuthController@registerGoogle');
    $router->get('/google/callback', 'AuthController@callbackGoogle');
    $router->get('/{id}/reset-email', 'AuthController@verifyChangeEmail');
    $router->get('/{id}/reset-password', 'AuthController@reset');
    $router->post('/forgot-password', 'AuthController@forgot');
    $router->post('/change-password', 'AuthController@change');
    $router->post('/reset-email', 'AuthController@changeEmail');
    $router->post('/logout', 'AuthController@logout');
    $router->post('/refresh-token', 'AuthController@refresh');
    
});

$router->group(['prefix' => 'authentication', 'middleware' => 'jwt_auth' ], function () use ($router) {
    $router->post('/change-email', 'AuthController@reqChangeEmail');
});


