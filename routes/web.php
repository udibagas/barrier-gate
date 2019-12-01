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

    Route::get('department/getList', 'DepartmentController@getList');
    Route::resource('department', 'DepartmentController')->only(['index', 'store', 'update', 'destroy']);

    Route::get('snapshots', 'SnapshotsController@index');
    Route::delete('snapshots', 'SnapshotsController@delete');

    Route::post('backup', 'BackupController@store');
    Route::get('backup', 'BackupController@index');
    Route::delete('backup', 'BackupController@destroy');
    Route::post('restoreDatabase', 'BackupController@restoreDatabase');
    Route::post('restoreSnapshot', 'BackupController@restoreSnapshot');

    Route::post('barrierGate/openGate/{barrierGate}', 'BarrierGateController@openGate');
    Route::post('barrierGate/testPrinter/{barrierGate}', 'BarrierGateController@testPrinter');
    Route::post('barrierGate/testCamera/{barrierGate}', 'BarrierGateController@testCamera');
    Route::get('barrierGate/getList', 'BarrierGateController@getList');
    Route::resource('barrierGate', 'BarrierGateController')->only(['index', 'store', 'update', 'destroy']);

    Route::get('user/search', 'UserController@search');
    Route::resource('user', 'UserController')->only(['index', 'store', 'update', 'destroy']);
    Route::resource('setting', 'SettingController')->only(['index', 'store', 'update']);
    Route::resource('accessLog', 'AccessLogController')->only(['index', 'store', 'update', 'destroy']);
    Route::resource('karcisHilang', 'KarcisHilangController')->only(['index', 'store', 'update', 'destroy']);
    Route::resource('bukaManual', 'BukaManualController')->only(['index', 'store', 'update', 'destroy']);

    // Report
    Route::get('report', 'ReportController@index');
    Route::get('report/terparkir', 'ReportController@terparkir');
    Route::get('report/bukaManual', 'ReportController@bukaManual');
    Route::get('report/tanpaKartu', 'ReportController@tanpaKartu');
    Route::get('report/karcisHilang', 'ReportController@karcisHilang');
});

Route::get('/{any}', 'AppController@index')->where('any', '.*');
