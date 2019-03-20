<?php

namespace Admin\Widgets;

use AdminTemplate;
use App\Models\BaccaratBet;
use App\Models\BaseModel;
use App\Models\DepositRequest;
use App\Models\RouletteBet;
use App\Models\User;
use App\Models\UserAuthorization;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use DB;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Widgets\Widget;

class DashboardMap extends Widget
{

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        $now = Carbon::now()->format(config('selectOptions.common.date'));

        $todayRegisteredUsersIds = User::forTodayOnly()->pluck('id')->all();
        $todayRegisteredUsersCount = count($todayRegisteredUsersIds);
        $todayRegisteredUsersDepositRequestsCount = DepositRequest::forTodayOnly()->whereIn('user_id', $todayRegisteredUsersIds)->whereStatus('succeed')->count();
        $todayRegisteredUsersDepositRequestsAmount = DepositRequest::forTodayOnly()->whereIn('user_id', $todayRegisteredUsersIds)->whereStatus('succeed')->sum('received_default_amount');
        $todayRegisteredUsersWithdrawalRequestsCount = WithdrawalRequest::forTodayOnly()->whereIn('user_id', $todayRegisteredUsersIds)->whereStatus('succeed')->count();
        $todayRegisteredUsersWithdrawalRequestsAmount = WithdrawalRequest::forTodayOnly()->whereIn('user_id', $todayRegisteredUsersIds)->whereStatus('succeed')->sum('received_default_amount');

        $todayActiveUsersIds = UserAuthorization::forTodayOnly()->select('user_id')->distinct()->pluck('user_id')->all();
        $todayActiveUsersCount = count($todayActiveUsersIds);
        $todayAllUsersDepositRequestsCount = DepositRequest::forTodayOnly()->whereStatus('succeed')->count();
        $todayAllUsersDepositRequestsAmount = DepositRequest::forTodayOnly()->whereStatus('succeed')->sum('received_default_amount');
        $todayAllUsersWithdrawalRequestsCount = WithdrawalRequest::forTodayOnly()->whereStatus('succeed')->count();
        $todayAllUsersWithdrawalRequestsAmount = WithdrawalRequest::forTodayOnly()->whereStatus('succeed')->sum('received_default_amount');

        $todayAllBaccaratBetsCount = BaccaratBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->count();
        $todayAllBaccaratBetsSum = BaccaratBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->sum('default_amount');
        $todayAllBaccaratOutcomeSum = BaccaratBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->sum('default_outcome');
        $todayWonBaccaratBetsCount = BaccaratBet::forTodayOnly()->where('status', 'won')->count();
        $todayWonBaccaratBetsSum = BaccaratBet::forTodayOnly()->where('status', 'won')->sum('default_amount');
        $todayWonBaccaratOutcomeSum = BaccaratBet::forTodayOnly()->where('status', 'won')->sum('default_outcome');

        $todayAllBaccaratProfitability = $todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum;
        //$todayAllBaccaratProfitabilityPercents = $todayAllBaccaratBetsSum ? round($todayAllBaccaratProfitability * 100 / $todayAllBaccaratBetsSum) : 0;
        $todayAllBaccaratProfitabilityPercents = $todayAllBaccaratBetsSum
            ? number_format(($todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum) / $todayAllBaccaratBetsSum * 100, 2)
            : 0;



        $todayAllRouletteBetsCount = RouletteBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->count();
        $todayAllRouletteBetsSum = RouletteBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->sum('default_amount');
        $todayAllRouletteOutcomeSum = RouletteBet::forTodayOnly()->whereIn('status', ['won', 'lost'])->sum('default_outcome');
        $todayWonRouletteBetsCount = RouletteBet::forTodayOnly()->where('status', 'won')->count();
        $todayWonRouletteBetsSum = RouletteBet::forTodayOnly()->where('status', 'won')->sum('default_amount');
        $todayWonRouletteOutcomeSum = RouletteBet::forTodayOnly()->where('status', 'won')->sum('default_outcome');

        $todayAllRouletteProfitability = $todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum;
        //$todayAllRouletteProfitabilityPercents = $todayAllRouletteBetsSum ? round($todayAllRouletteProfitability * 100 / $todayAllRouletteBetsSum) : 0;
        $todayAllRouletteProfitabilityPercents = $todayAllRouletteBetsSum
            ? number_format(($todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum) / $todayAllRouletteBetsSum * 100, 2)
            : 0;

        /*$todayTotalProfitabilityPercents = $todayAllBaccaratBetsSum && $todayAllRouletteBetsSum ?
           round(($todayAllBaccaratProfitability + $todayAllRouletteProfitability) * 100 / ($todayAllBaccaratBetsSum + $todayAllRouletteBetsSum)) : 0;*/

        $todayTotalProfitabilityPercents = $todayAllBaccaratBetsSum || $todayAllRouletteBetsSum
            ? number_format((($todayAllBaccaratBetsSum + $todayAllRouletteBetsSum) - ($todayAllBaccaratOutcomeSum + $todayAllRouletteOutcomeSum)) / ($todayAllBaccaratBetsSum + $todayAllRouletteBetsSum) * 100)
            : 0;

        $todayRegisteredUsersUrl = BaseModel::generateUrl(User::class, ['date_from' => $now, 'date_to' => $now]);
        $todayRegisteredUsersDepositRequestsUrl =
            BaseModel::generateUrl(DepositRequest::class, ['date_from' => $now, 'date_to' => $now, 'status' => 'succeed', 'todayRegisteredUsers' => true]);
        $todayRegisteredUsersWithdrawalRequestsUrl =
            BaseModel::generateUrl(WithdrawalRequest::class, ['date_from' => $now, 'date_to' => $now, 'status' => 'succeed', 'todayRegisteredUsers' => true]);
        $todayActiveUsersUrl = BaseModel::generateUrl(User::class,
            ['ids' => $todayActiveUsersIds, 'bets_date_from' => Carbon::now()->subDays(1), 'bets_date_to' => Carbon::now(), 'dateTime' => true]);
        $todayAllUsersDepositRequestsUrl = BaseModel::generateUrl(DepositRequest::class, ['date_from' => $now, 'date_to' => $now, 'status' => 'succeed']);
        $todayAllUsersWithdrawalRequestsUrl = BaseModel::generateUrl(WithdrawalRequest::class, ['date_from' => $now, 'date_to' => $now, 'status' => 'succeed']);

        $todayAllBaccaratBetsUrl = BaseModel::generateUrl(BaccaratBet::class, ['date_from' => $now, 'date_to' => $now, 'statuses' => ['won', 'lost']]);
        $todayWonBaccaratBetsUrl = BaseModel::generateUrl(BaccaratBet::class, ['date_from' => $now, 'date_to' => $now, 'statuses' => ['won']]);

        $todayAllRouletteBetsUrl = BaseModel::generateUrl(RouletteBet::class, ['date_from' => $now, 'date_to' => $now, 'statuses' => ['won', 'lost']]);
        $todayWonRouletteBetsUrl = BaseModel::generateUrl(RouletteBet::class, ['date_from' => $now, 'date_to' => $now, 'statuses' => ['won']]);

        return view('admin::dashboard', get_defined_vars());
    }

    /**
     * @return string|array
     */
    public function template()
    {
        return AdminTemplate::getViewPath('dashboard');
    }

    /**
     * @return string
     */
    public function block()
    {
        return 'block.top';
    }
}