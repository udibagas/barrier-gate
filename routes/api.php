<?php

Route::resource('/accessLog', 'AccessLogController')->only(['store', 'update']);
Route::get('/user/search', 'UserController@search');
Route::get('/setting', 'SettingController@index');
