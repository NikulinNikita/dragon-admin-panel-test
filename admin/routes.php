<?php

use Illuminate\Routing\Router;

Route::group(['as' => 'admin.', 'namespace' => 'Admin\Http\Controllers'], function (Router $router) {
    Route::get('', [
        'as' => 'dashboard',
        function () {
            if (auth()->user()->isAbleTo(['manage_everything', 'manage_dashboard'])) {
                return app()->call("Admin\Http\Controllers\AdminController@getDashboard");
            }

            if (count(auth()->user()->roles) && count(auth()->user()->roles->first()->permissions)) {
                return redirect('/admin_panel/' . str_replace('manage_', '', auth()->user()->roles->first()->permissions->first()->name));
            }

            return redirect('/admin_panel/deposit_requests');
        }
    ]);

    Route::group(['middleware' => 'roles:superadmin|general|manager'], function () {
        Route::get('resource/down', 'ProcessArtisanController@down')->name('resource.down');
        Route::get('resource/up', 'ProcessArtisanController@up')->name('resource.up');
    });

    Route::group(['middleware' => 'roles:superadmin|general|accountant'], function () {
        Route::get('user/auth/{id}', 'User\AuthController@manualLogin')->name('user.manual_login');
    });

    Route::group(['middleware' => 'perms:manage_partnership_settings'], function () {
        Route::get('partnership_settings', 'PartnershipController@index');
        Route::post('partnership_settings', 'PartnershipController@update');
    });

    Route::group(['middleware' => 'perms:manage_everything|manage_baccarat_rounds|manage_roulette_rounds'], function () {
//        Route::get('rounds/{type}/{id}/stop', 'RoundsController@stop')->name('rounds.stop')->where('id', '[0-9]+');
//        Route::get('rounds/{type}/{id}/manipulate', 'RoundsController@manipulateRound')->name('rounds.manipulate')->where('id', '[0-9]+');
        Route::get('rounds/{type}/{id}/restart', 'RoundsController@restart')->name('rounds.restart')->where('id', '[0-9]+');
        Route::get('rounds/{type}/{id}/restartWithNoBets', 'RoundsController@restartWithNoBets')->name('rounds.restartWithNoBets')->where('id', '[0-9]+');
        Route::get('rounds/{type}/{id}/refundBets', 'RoundsController@refundBets')->name('rounds.refundBets')->where('id', '[0-9]+');
    });

    Route::group(['middleware' => 'reports'], function () {
        Route::get('reports/{report}', 'ReportsController@getReports')->name('getReports');
        Route::get('reports/{report}/reGenerateReport', 'ReportsController@reGenerateReport')->name('reports.reGenerateReport');
        Route::post('reports/{adminModel}/exportReport', 'ExportController@exportReport')->name('getExportReport');
        Route::get('reports/{report}/exportStaticReport', 'ExportController@exportStaticReport')->name('getExportStaticReport');
    });

    Route::group(['middleware' => 'perms:manage_everything|manage_exchange_rates'], function () {
        Route::get('currencies/fetch', 'SessionController@fetchCurrencies')->name('currencies.fetch');
    });

    Route::group(['middleware' => 'perms:manage_everything|manage_dashboard'], function () {
        Route::get('ajax/getChartData', 'AjaxController@getChartData')->name('getChartData');
        Route::get('ajax/appendData', 'AjaxController@appendData')->name('appendData');
    });

    Route::group(['middleware' => 'perms:manage_everything|manage_mc_chats'], function () {
        Route::any('vue/{section}/{method}/{id?}', 'VueController@fireStaticMethod')->where('id', '[0-9]+');
    });

    Route::post('ajax/updateColumnEditable', 'AjaxController@updateColumnEditable')->name('updateColumnEditable');
    Route::get('ajax/getCustomAjaxSelectOptions', 'SelectAjaxController@getCustomAjaxSelectOptions')->name('getCustomAjaxSelectOptions');
    Route::post('ajax/getSelectAjaxOptions', 'SelectAjaxController@getSelectAjaxOptions')->name('getSelectAjaxOptions');

    Route::get('locale/switch', 'PagesController@switchLocale')->name('locale.switch');
    Route::get('currency/switch', 'PagesController@switchCurrency')->name('currency.switch');
    Route::post('search', 'PagesController@search')->name('search');

    Route::get('user/notifications', 'User\NotificationsController@index');
    Route::get('user/notifications/readAll', 'User\NotificationsController@readAll');
    Route::patch('user/notifications/{staff}/read', 'User\NotificationsController@read');
    Route::patch('user/notifications/{staff}/read/{notification_id}', 'User\NotificationsController@read');
    Route::patch('user/notifications/{staff}/unread', 'User\NotificationsController@unread');
    Route::patch('user/notifications/{staff}/unread/{notification_id}', 'User\NotificationsController@unread');

    Route::get('reports/agent/{parent_id}/subagents', 'AgentController@getSubAgents');
    Route::get('agents/{parent_id}/rounds/{player_id}', 'AgentController@reportsByRoundPage')->name('agents.betsbank.rounds');
});