<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

Route::get('/portal', 'PortalController@index');

// Posts List
Route::get('/posts', 'PostController@getList');
Route::get('/post/{id}', 'PostController@show');
Route::get('/post/{id}/torrent/download', 'PostController@torrentDownload');

Route::get('/user', 'UserController@hello');

Route::get('/phpinfo', function () {
    ob_start();
    phpinfo();
    $contents = ob_get_clean();
    return $contents;
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/user/{id}/avatar', function(){
    return response()->file(storage_path('app/private/avatar/default.jpg'));
})->name('user.avatar');