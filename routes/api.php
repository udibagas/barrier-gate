<?php

Route::get('/accessLog/search', 'AccessLogController@search');
Route::resource('/accessLog', 'AccessLogController')->only(['store', 'update']);
Route::get('/user/search', 'UserController@search');
Route::get('/setting', 'SettingController@index');
Route::get('/barrierGate/search', 'BarrierGateController@search');
Route::get('barrierGate/takeSnapshot/{barrierGate}', 'BarrierGateController@takeSnapshot');
