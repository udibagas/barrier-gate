<?php

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
Route::post('login', 'AuthController@login');

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('checkAuth', 'AppController@checkAuth');
    Route::get('getNavigation', 'AppController@getNavigation');
    Route::post('logout', 'AuthController@logout');
    Route::resource('department', 'DepartmentController')->only(['index', 'store', 'update', 'destroy']);
    Route::resource('user', 'UserController')->only(['index', 'store', 'update', 'destroy']);
});

Route::get('/{any}', 'AppController@index')->where('any', '.*');
