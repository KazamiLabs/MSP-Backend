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

    Route::post('login', 'Admin\AuthController@login');
    Route::post('logout', 'Admin\AuthController@logout');
    Route::post('refresh', 'Admin\AuthController@refresh');
    Route::get('me', 'Admin\AuthController@me');

});

// Post Admin
Route::get('/posts/admin', 'Admin\PostController@getList')->middleware('auth');
Route::get('/post/{id}/admin', 'Admin\PostController@show')->middleware('auth');
Route::post('/post/admin', 'Admin\PostController@add')->middleware('auth');
Route::post('/post/{id}/admin', 'Admin\PostController@update')->middleware('auth');
Route::post('/post/picture/admin', 'Admin\PostController@uploadPic')->middleware('auth');
Route::post('/post/torrent/admin', 'Admin\PostController@uploadTorrent')->middleware('auth');

// Post Portal
Route::get('/posts', 'PostController@getList');
Route::get('/post/{id}', 'PostController@show');
