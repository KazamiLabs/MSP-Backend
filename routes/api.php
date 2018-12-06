<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/message', 'MessageLogsController@uploadMessage');

// Search
Route::get('/search/user', 'UserController@search');

// Auth Admin
Route::group([
    'prefix' => 'auth',
], function ($router) {
    $router->post('login', 'Admin\AuthController@login');
    $router->post('logout', 'Admin\AuthController@logout');
    $router->post('refresh', 'Admin\AuthController@refresh');
    $router->get('me', 'Admin\AuthController@me');
});

Route::middleware('refresh.token')->group(function ($router) {
    // Post Admin
    $router->get('/posts/admin', 'Admin\PostController@getList');
    $router->get('/post/{id}/admin', 'Admin\PostController@show');
    $router->post('/post/admin', 'Admin\PostController@add');
    $router->post('/post/{id}/admin', 'Admin\PostController@update');
    $router->post('/post/picture/admin', 'Admin\PostController@uploadPic');
    $router->post('/post/torrent/admin', 'Admin\PostController@uploadTorrent');
});
// Post Admin
// Route::get('/posts/admin', 'Admin\PostController@getList');
// Route::get('/post/{id}/admin', 'Admin\PostController@show');
// Route::post('/post/admin', 'Admin\PostController@add');
// Route::post('/post/{id}/admin', 'Admin\PostController@update');
// Route::post('/post/picture/admin', 'Admin\PostController@uploadPic');
// Route::post('/post/torrent/admin', 'Admin\PostController@uploadTorrent');

// Post Portal
Route::get('/posts', 'PostController@getList');
Route::get('/post/{id}', 'PostController@show');
