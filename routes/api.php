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
], function ($route) {
    $route->post('login', 'Admin\AuthController@login');
    $route->post('logout', 'Admin\AuthController@logout');
    $route->get('refresh', 'Admin\AuthController@refresh');
    $route->get('me', 'Admin\AuthController@me');
});

Route::middleware('refresh.token')->namespace('Admin')->group(function ($route) {
    // Posts Manage
    $route->get('/posts/admin', 'PostController@getList');
    $route->get('/post/queues/admin', 'PostController@queues');
    $route->get('/post/{id}/admin', 'PostController@show');
    $route->post('/post/admin', 'PostController@add');
    $route->post('/post/{id}/admin', 'PostController@update');
    $route->post('/post/{id}/admin/status', 'PostController@changeStatus');
    $route->delete('/post/{id}/admin', 'PostController@deletePost');
    // $route->post('/post/picture/admin', 'PostController@uploadPic');
    // $route->post('/post/torrent/admin', 'PostController@uploadTorrent');
    // Bangumi
    $route->get('/bangumi/{post_id}/transfer-log/admin', 'BangumiController@transferLog');
    $route->get('/bangumi/{post_id}/transfer-log-raw/admin', 'BangumiController@transferLogRaw');
    // Users Manage
    $route->get('/users/admin', 'UserController@getList');
    $route->get('/user/{id}/admin', 'UserController@show');
    $route->post('/user/admin', 'UserController@add');
    $route->post('/user/{id}/admin', 'UserController@update');
    // Settings
    $route->get('/bangumi-settings/admin', 'SettingController@bangumiSettings');
    // $route->post('/bangumi-setting/admin', '');
    $route->post('/bangumi-setting/{id}/admin', 'SettingController@updateBangumiSettings');
    $route->post('/bangumi-setting/{id}/admin/status', 'SettingController@changeBangumiSettingStatus');
    $route->delete('/bangumi-setting/{id}/admin', 'SettingController@deleteBangumiSettings');
});
// Posts Manage
// Route::get('/posts/admin', 'Admin\PostController@getList');
// Route::get('/post/{id}/admin', 'Admin\PostController@show');
// Route::post('/post/admin', 'Admin\PostController@add');
// Route::post('/post/{id}/admin', 'Admin\PostController@update');
Route::post('/post/picture/admin', 'Admin\PostController@uploadPic');
Route::post('/post/torrent/admin', 'Admin\PostController@uploadTorrent');
