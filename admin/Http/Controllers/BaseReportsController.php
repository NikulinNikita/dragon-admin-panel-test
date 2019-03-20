<?php

namespace Admin\Http\Controllers;

use AdminSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MessagesStack;

class BaseReportsController extends Controller
{
    public function reGenerateReport(Request $request, string $table)
    {
        $table = ucwords(camel_case($table));

        $generatedItems = app()->call("App\Console\Commands\Generate{$table}@handle", ['dateScope' => $request->get('date') ?? 'ForTodayOnly']);
        MessagesStack::addSuccess("Generated <b style='font-size: 16px;'>{$generatedItems}</b> items!");

        return redirect()->back();
    }

    public function getReports(Request $request, $page, $exportToExcel = null)
    {
        $request = $request->merge(['page' => $page, 'date_from' => $request->get('date_from'), 'date_to' => $request->get('date_to')]);

        return $this->callBaseReportLayout($request, $exportToExcel, function ($request, $dateFrom, $dateTo) {
            return app()->call("Admin\Http\Controllers\ReportsController@get{$request->get('functionName')}", ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
        });
    }

    protected function callBaseReportLayout($request, $exportToExcel, callable $callback)
    {
        $user               = auth()->user();
        $page               = $request->get('page');
        $functionName       = ucwords(camel_case($page));
        $pageHeaderForExcel = preg_replace("/(?<=\\w)(?=[A-Z])/", " $1", $functionName);
        $pageHeader         = ! strpos(trans("admin/{$page}.page_title"), "{$page}.") ? trans("admin/{$page}.page_title") : $pageHeaderForExcel;
        $request            = $request = $request->merge(['functionName' => $functionName]);
        $dateFrom           = $request->get('date_from') && ($user->isAbleTo(['manage_everything']) || $user->hasAnyRole(['general', 'accountant'])) ?
            Carbon::parse($request->get('date_from')) : Carbon::today()->subDay();
        $dateTo             = $request->get('date_to') && ($user->isAbleTo(['manage_everything']) || $user->hasAnyRole(['general', 'accountant'])) ?
            Carbon::parse($request->get('date_to'))->addDay()->subSecond() : Carbon::today()->addDay()->subSecond();
        $currencyId         = $request->get('currency_id') ? $request->get('currency_id') : null;
        $valueFrom          = $request->get('value_from') ? $request->get('value_from') : null;
        $valueTo            = $request->get('value_to') ? $request->get('value_to') : null;
        $paramsArr          = [
            'pageHeader'  => $pageHeaderForExcel,
            'page'        => $page,
            'date_from'   => $dateFrom->format(config('selectOptions.common.date')),
            'date_to'     => $dateTo->format(config('selectOptions.common.date')),
            'currency_id' => $currencyId,
            'value_from'  => $valueFrom,
            'value_to'    => $valueTo,
        ];

        extract(call_user_func($callback, $request, $dateFrom, $dateTo));

        if ($request->get('exportToExcel') || $exportToExcel) {
            return view("admin::pages.{$page}", get_defined_vars());
        }

        return AdminSection::view(view("admin::pages.{$page}", get_defined_vars()));
    }
}