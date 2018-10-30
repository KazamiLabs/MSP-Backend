<?php

use Illuminate\Http\Request;

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
Route::get('/posts', 'PostController@getList');
Route::get('/post/{id}', 'PostController@show');
Route::get('/posts/admin', 'Admin\PostController@getList');
Route::get('/posts/{id}/admin', 'Admin\PostController@show');
Route::get('/search/user', 'UserController@search');
