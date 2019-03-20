<?php

// Authentication Routes...
Route::get('login', 'AdminAuth\LoginController@showLoginForm')->name('adminLogin');
Route::post('login', 'AdminAuth\LoginController@login')->name('adminAuth.postLogin');
Route::get('logout', 'AdminAuth\LoginController@logout')->name('adminLogout');

// Registration Routes...
Route::get('register', 'AdminAuth\RegisterController@showRegistrationForm')->name('adminRegister');
Route::post('register', 'AdminAuth\RegisterController@register')->name('adminAuth.postRegister');

// Password Reset Routes...
Route::get('password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm')->name('adminPassword.request');
Route::post('password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail')->name('adminPassword.email');
Route::get('password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm')->name('adminPassword.reset');
Route::post('password/reset', 'AdminAuth\ResetPasswordController@reset')->name('adminPassword.postReset');

