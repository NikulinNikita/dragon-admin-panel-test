<?php

Route::get('/timer/{tableId?}', 'PagesController@timer')->name('timer');

Route::group(['middleware' => ['localeSessionRedirect', 'localizationRedirect']], function() {
    Route::get('/', 'PagesController@index')->name('index');
    Route::get('/home', 'PagesController@home')->name('home');

    Route::resource('table', 'Table\TableController');

    Route::group(['middleware' => ['auth', 'roles:superadmin|general|manager']], function() {
        Route::get('manager/tables', 'PagesController@tables')->name('manager.tables');
        Route::get('manager/broadcast', 'PagesController@broadcast')->name('manager.broadcast');
    });

    Route::get('/test', function() {
        $notification = new \App\Notifications\User\PersonalNotification(
            [
                'title'   => 'Hello',
                'message' => 'World',
                'displayParams' => ['type' => 'bank']
            ]
        );

        (new \Admin\Services\User\PersonalNotificationService(auth()->user(), $notification))->send();
    });
});