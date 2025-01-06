<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

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
// $app->group(['prefix' => 'api'], function () use ($app) {
//     $app->get('posts', 'PostController@index');
//     $app->post('posts-save', ['middleware' => 'json', 'uses' => 'PostController@store']);
//     $app->get('posts/{id}', 'PostController@show');
//     $app->put('posts-update/{id}', ['middleware' => 'json', 'uses' => 'PostController@update']);
//     $app->delete('posts-delete/{id}', 'PostController@destroy');
//     $app->get('/blogs/search', 'PostController@search');
// });

Route::get('/posts', [PostController::class, 'index']);
Route::get('/delete-cache', [PostController::class, 'clearCache']);

Route::get('/get-cache', [PostController::class, 'getCache']);
 
// Route::controller(PostController::class)->group(function () {
//     Route::get('/posts', 'index');
// });