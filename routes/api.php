<?php

use Illuminate\Http\Request;


use App\Images;

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

Route::get('/meme/all', 'ImageController@index');

Route::get('/meme/id/{image}', 'ImageController@show');

Route::get('/meme/page/{pages}', 'ImageController@byPage');

Route::get('meme/popular', 'ImageController@byPopular');

Route::post('meme/create', 'ImageController@create');
