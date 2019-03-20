<?php

Route::get('/user/activateSession/{table}', 'ApiUserController@activateSession')->middleware('auth:api');
Route::get('/user/deactivateSession/{table}', 'ApiUserController@deactivateSession')->middleware('auth:api');

Route::get('/dealer/roundStop', 'ApiDealerController@roundStop')->middleware('auth:api');
Route::get('/dealer/getActiveSession/{id}', 'ApiDealerController@getActiveSession');
Route::post('/dealer/dealerCommand', 'ApiDealerController@dealerCommand')->middleware('auth:api');

Route::apiResource('user', 'ApiUserController')->middleware('auth:api');

Route::get('/jwt/{user}/login', 'Auth\JwtTokenController@login');
Route::post('/jwt/{user}/logout', 'Auth\JwtTokenController@logout');