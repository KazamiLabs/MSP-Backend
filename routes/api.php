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
    $router->get('refresh', 'Admin\AuthController@refresh');
    $router->get('me', 'Admin\AuthController@me');
});

Route::middleware('refresh.token')->group(function ($router) {
    // Posts Manage
    $router->get('/posts/admin', 'Admin\PostController@getList');
    $router->get('/post/queues/admin', 'Admin\PostController@queues');
    $router->get('/post/{id}/admin', 'Admin\PostController@show');
    $router->post('/post/admin', 'Admin\PostController@add');
    $router->post('/post/{id}/admin', 'Admin\PostController@update');
    $router->post('/post/{id}/admin/status', 'Admin\PostController@changeStatus');
    $router->delete('/post/{id}/admin', 'Admin\PostController@deletePost');
    // $router->post('/post/picture/admin', 'Admin\PostController@uploadPic');
    // $router->post('/post/torrent/admin', 'Admin\PostController@uploadTorrent');
    // Users Manage
    $router->get('/users/admin', 'Admin\UserController@getList');
    $router->get('/user/{id}/admin', 'Admin\UserController@show');
    $router->post('/user/admin', 'Admin\UserController@add');
    $router->post('/user/{id}/admin', 'Admin\UserController@update');
    // Settings
    $router->get('/bangumi-settings/admin', 'Admin\SettingController@bangumiSettings');
    // $router->post('/bangumi-setting/admin', '');
    $router->post('/bangumi-setting/{id}/admin', 'Admin\SettingController@updateBangumiSettings');
    $router->post('/bangumi-setting/{id}/admin/status', 'Admin\SettingController@changeBangumiSettingStatus');
    $router->delete('/bangumi-setting/{id}/admin', 'Admin\SettingController@deleteBangumiSettings');
});
// Posts Manage
// Route::get('/posts/admin', 'Admin\PostController@getList');
// Route::get('/post/{id}/admin', 'Admin\PostController@show');
// Route::post('/post/admin', 'Admin\PostController@add');
// Route::post('/post/{id}/admin', 'Admin\PostController@update');
Route::post('/post/picture/admin', 'Admin\PostController@uploadPic');
Route::post('/post/torrent/admin', 'Admin\PostController@uploadTorrent');

// Posts List
Route::get('/posts', 'PostController@getList');
Route::get('/post/{id}', 'PostController@show');
