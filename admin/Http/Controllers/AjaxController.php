<?php

namespace Admin\Http\Controllers;

use App\Models\BaccaratBet;
use App\Models\DepositRequest;
use App\Models\RouletteBet;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function getChartData(Request $request)
    {
        $type     = $request->get('type');
        $dateFrom = Carbon::parse($request->get('date_from'));
        $dateTo   = Carbon::parse($request->get('date_to'))->addDay();

        if ($type === 'registeredUsersCount') {
            $result = User::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)
                          ->selectRaw("COUNT(id) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                          ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'DepositRequestsCount') {
            $result = DepositRequest::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'succeed')
                                    ->selectRaw("COUNT(id) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                    ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'DepositRequestsAmount') {
            $result = DepositRequest::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'succeed')
                                    ->selectRaw("SUM(received_default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                    ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'AllBaccaratBetsAmount') {
            $result = BaccaratBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->whereIn('status', ['won', 'lost'])
                                 ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                 ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'WonBaccaratBetsAmount') {
            $result = BaccaratBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'won')
                                 ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                 ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'AllBaccaratProfitabilityPercents') {
            $allBaccaratBetsAmount = BaccaratBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->whereIn('status', ['won', 'lost'])
                                                ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                                ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
            $wonBaccaratBetsAmount = BaccaratBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'won')
                                                ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                                ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
            $result                = [];
            foreach ($allBaccaratBetsAmount as $date => $val) {
                $result[$date] = array_get($allBaccaratBetsAmount, $date) && array_get($wonBaccaratBetsAmount, $date) ?
                    round(($allBaccaratBetsAmount[$date] - $wonBaccaratBetsAmount[$date]) * 100 / $allBaccaratBetsAmount[$date]) : 0;
            }
        } elseif ($type === 'AllRouletteBetsAmount') {
            $result = RouletteBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->whereIn('status', ['won', 'lost'])
                                 ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                 ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'WonRouletteBetsAmount') {
            $result = RouletteBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'won')
                                 ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                 ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
        } elseif ($type === 'AllRouletteProfitabilityPercents') {
            $allRouletteBetsAmount = RouletteBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->whereIn('status', ['won', 'lost'])
                                                ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                                ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
            $wonRouletteBetsAmount = RouletteBet::where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)->where('status', 'won')
                                                ->selectRaw("SUM(default_amount) AS `amount`")->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') AS `Date`")
                                                ->orderBy('Date', 'asc')->groupBy('Date')->pluck('amount', 'Date')->all();
            $result                = [];
            foreach ($allRouletteBetsAmount as $date => $val) {
                $result[$date] = array_get($allRouletteBetsAmount, $date) && array_get($wonRouletteBetsAmount, $date) ?
                    round(($allRouletteBetsAmount[$date] - $wonRouletteBetsAmount[$date]) * 100 / $allRouletteBetsAmount[$date]) : 0;
            }
        } else {
            $result = [];
        }

        return json_encode($result);
    }

    public function appendData(Request $request)
    {
        list($id, $type) = array_values($request->all());

        return view('admin::defaults._appendContentBlock', get_defined_vars());
    }

    public function updateColumnEditable(Request $request)
    {
        ['pk' => $id, 'value' => $value, 'name' => $columnName] = $request->all();
        [$modelName, $column] = explode('.', $columnName);
        [$modelName, $filterColumn] = explode('_', $modelName);
        $modelName = str_replace('Admin', '', $modelName);
        $model     = app()->make("App\\Models\\{$modelName}")->whereId($id)->first();

        if (in_array($modelName, ['BaccaratResult', 'RouletteResult'])) {
            ["table_limit_id" => $table_limit_id] = $request->all();
            $targetModel = $model->resultLimitCurrencies->filterFix('tableLimitCurrency.tableLimit.id', '=', $table_limit_id);
            DB::transaction(function () use ($filterColumn, $targetModel, $column, $value) {
                if ($filterColumn !== 'ALL') {
                    $targetModel = $targetModel->filterFix('tableLimitCurrency.currency.code', '=', $filterColumn)->first();
                    $targetModel->update([$column => $value]);
                } else {
                    foreach ($targetModel as $item) {
                        $item->update([$column => $value]);
                    }
                }
            });
        }

        return response('Success!');
    }
}
