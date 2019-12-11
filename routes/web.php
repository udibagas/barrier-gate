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

    // Barrier gate related
    Route::get('barrierGate/takeSnapshot/{barrierGate}', 'BarrierGateController@takeSnapshot');
    Route::post('barrierGate/testPrinter/{barrierGate}', 'BarrierGateController@testPrinter');
    Route::post('barrierGate/testCamera/{barrierGate}', 'BarrierGateController@testCamera');
    Route::get('barrierGate/getList', 'BarrierGateController@getList');
    Route::resource('barrierGate', 'BarrierGateController')->only(['index', 'store', 'update', 'destroy']);

    Route::get('user/search', 'UserController@search');
    Route::resource('user', 'UserController')->only(['index', 'store', 'update', 'destroy']);
    Route::resource('setting', 'SettingController')->only(['index', 'store', 'update']);

    Route::put('karcisHilang/sudahDiambil/{karcisHilang}', 'KarcisHilangController@sudahDiambil');
    Route::resource('karcisHilang', 'KarcisHilangController')->only(['index', 'store', 'update', 'destroy']);

    Route::resource('bukaManual', 'BukaManualController')->only(['index', 'store', 'update', 'destroy']);

    // Access Log Related
    Route::get('accessLog/getQueue', 'AccessLogController@getQueue');
    Route::put('accessLog/setSudahKeluar/{accessLog}', 'AccessLogController@setSudahKeluar');
    Route::put('accessLog/setSudahKeluarSemua', 'AccessLogController@setSudahKeluarSemua');
    // Route::get('accessLog/index', 'AccessLogController@index');
    Route::resource('accessLogs', 'AccessLogController')->only(['index', 'store', 'update', 'destroy']);

    // Report
    Route::get('report', 'ReportController@index');
    Route::get('report/terparkir', 'ReportController@terparkir');
    Route::get('report/bukaManual', 'ReportController@bukaManual');
    Route::get('report/tanpaKartu', 'ReportController@tanpaKartu');
    Route::get('report/karcisHilang', 'ReportController@karcisHilang');

    Route::post('notification', 'NotificationController@store');
    Route::get('notification/unread', 'NotificationController@unread');
    Route::get('notification', 'NotificationController@index');
    Route::put('notification/markAsRead/{id}', 'NotificationController@markAsRead');
    Route::put('notification/markAllAsRead', 'NotificationController@markAllAsRead');
    Route::delete('notification/clear', 'NotificationController@clear');
    Route::delete('notification/{id}', 'NotificationController@destroy');
});

Route::get('/{any}', 'AppController@index')->where('any', '.*');
