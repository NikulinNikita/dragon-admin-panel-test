@inject('User', '\App\Models\User')
@inject('UserSession', 'App\Models\UserSession')
@inject('UserBonus', 'App\Models\UserBonus')
@inject('Operation', 'App\Models\Operation')
@inject('BetsBankAccrual', 'App\Models\BetsBankAccrual')
@inject('BonusTypeBetsAmount', 'DragonStudio\BonusProgram\Types\BonusTypeBetsAmount')

<div class="">
	@if($type === 'user_main_stats')
        <?php
        $table = 'users';
        $instance = $User::findOrFail($id);

        $gamingDuration = $UserSession::where('user_id', $id)->selectRaw("SUM(TIMESTAMPDIFF(second, created_at, updated_at)) AS `amount`")->first()->amount;
        $totalGamingDuration = round($gamingDuration / 3600, 1);

        $succeedDepositRequests = $instance->depositRequests->where('status', 'succeed');
        $totalAmountSumOfDeposits = BaseModel::formatCurrency($instance->currency_id, $succeedDepositRequests->sum('received_amount'));
        $totalDefaultAmountSumOfDeposits = BaseModel::exchangeCurrency($succeedDepositRequests->sum('received_default_amount'));

        $succeedWithdrawalRequests = $instance->withdrawalRequests->where('status', 'succeed');
        $totalAmountSumOfWithdrawals = BaseModel::formatCurrency($instance->currency_id, $succeedWithdrawalRequests->sum('received_amount'));
        $totalDefaultAmountSumOfWithdrawals = BaseModel::exchangeCurrency($succeedWithdrawalRequests->sum('received_default_amount'));

        $totalAmountDifference = BaseModel::formatCurrency($instance->currency_id, $succeedDepositRequests->sum('received_amount') -
                                                                                   $succeedWithdrawalRequests->sum('received_amount'));
        $totalDefaultAmountDifference = BaseModel::exchangeCurrency($succeedDepositRequests->sum('received_default_amount') -
                                                                    $succeedWithdrawalRequests->sum('received_default_amount'));

        $userBonusTill = $instance->userTills->where('till_id', 2)->first();
        $userBonusesAmount = BaseModel::formatCurrency($instance->currency_id, $userBonusTill->balance);
        $userBonusesDefaultAmount = BaseModel::exchangeCurrency($userBonusTill->default_amount);

        $usedUserBonuses = $UserBonus->where('user_id', $instance->id)->whereNotNull('applied_at')->sum('amount');
        $usedUserBonusesAmount = BaseModel::formatCurrency($instance->currency_id, $usedUserBonuses);
        $usedUserBonusesDefaultAmount = BaseModel::convertToDefaultCurrencyAndExchangeCurrency($instance->currency_id, $usedUserBonuses);

        $userStatus = $instance->userStatusPoint->userStatus ? $instance->userStatusPoint->userStatus->title : trans("admin/common.no");

        $lastBetDate = count($instance->baccaratBets) ? $instance->baccaratBets->sortByDesc('created_at')->first()->created_at : trans("admin/common.no");

        $allBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->AllBetsAmount);
        $allBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->AllBetsDefaultAmount);

        $wonBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->WonBetsOutcome);
        $wonBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->WonBetsDefaultOutcome);

        $userBalance = BaseModel::formatCurrency($instance->currency_id, $instance->MoneyTill->balance);
        $userDeafultBalance = BaseModel::exchangeCurrency($instance->MoneyTill->default_amount);

        /*=======*/

        $allBaccaratPlayedBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratAmountSum);
        $allBaccaratPlayedBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultAmountSum);

        $allRoulettePlayedBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedRouletteAmountSum);
        $allRoulettePlayedBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->playedRouletteDefaultAmountSum);

        $allTotalPlayedBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratAmountSum + $instance->playedRouletteAmountSum);
        $allTotalPlayedBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultAmountSum + $instance->playedRouletteDefaultAmountSum);


        $playedBaccaratWinningSum        = $instance->playedBaccaratAmountSum - $instance->playedBaccaratOutcomeSum;
        $playedBaccaratDefaultWinningSum = $instance->playedBaccaratDefaultAmountSum - $instance->playedBaccaratDefaultOutcomeSum;

        $inversePlayedBaccaratWinningSum        = $playedBaccaratWinningSum < 0 ? abs($playedBaccaratWinningSum) : -$playedBaccaratWinningSum;
        $inversePlayedBaccaratDefaultWinningSum = $playedBaccaratDefaultWinningSum < 0 ? abs($playedBaccaratDefaultWinningSum) : -$playedBaccaratDefaultWinningSum;


        $allBaccaratWinningSum        = BaseModel::formatCurrency($instance->currency_id, $inversePlayedBaccaratWinningSum);
        $allBaccaratDefaultWinningSum = BaseModel::exchangeCurrency($inversePlayedBaccaratDefaultWinningSum);


        $playedRouletteWinningSum       = $instance->playedRouletteAmountSum - $instance->playedRouletteOutcomeSum;
        $playedRouetteDefaultWinningSum = $instance->playedRouletteDefaultAmountSum - $instance->playedRouletteDefaultOutcomeSum;

        $inversePlayedRouletteWinningSum       = $playedRouletteWinningSum < 0 ? abs($playedRouletteWinningSum) : -$playedRouletteWinningSum;
        $inversePlayedRouetteDefaultWinningSum = $playedRouetteDefaultWinningSum < 0 ? abs($playedRouetteDefaultWinningSum) : -$playedRouetteDefaultWinningSum;

        $allRouletteWinningSum        = BaseModel::formatCurrency($instance->currency_id, $inversePlayedRouletteWinningSum);
        $allRouletteDefaultWinningSum = BaseModel::exchangeCurrency(($inversePlayedRouetteDefaultWinningSum + $playedRouetteDefaultWinningSum));

        $allPlayedWinningSum = $playedBaccaratWinningSum + $playedRouletteWinningSum;
        $allPlayingWinningDefaultSum = $playedBaccaratDefaultWinningSum + $playedRouetteDefaultWinningSum;

        $inverseAllPlayedWinningSum         = $allPlayedWinningSum < 0 ? abs($allPlayedWinningSum) : -$allPlayedWinningSum;
        $inverseAllPlayingWinningDefaultSum = $allPlayingWinningDefaultSum < 0 ? abs($allPlayingWinningDefaultSum) : -$allPlayingWinningDefaultSum;

        $allWinningSum = BaseModel::formatCurrency($instance->currency_id, $inverseAllPlayedWinningSum);
        $allDefaultWinningSum = BaseModel::exchangeCurrency($inverseAllPlayingWinningDefaultSum);

        $allBaccaratPlayedBetsOutcomeSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratOutcomeSum);
        $allBaccaratPlayedBetsDefaultOutcomeSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultOutcomeSum);

        $allRoulettePlayedBetsOutcomeSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedRouletteOutcomeSum);
        $allRoulettePlayedBetsDefaultOutcomeSum = BaseModel::exchangeCurrency($instance->playedRouletteDefaultOutcomeSum);

        $allTotalPlayedBetsDefaultOutcomeSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratOutcomeSum + $instance->playedRouletteOutcomeSum);
        $allTotalPlayedBetsDefaultDefaultOutcomeSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultOutcomeSum + $instance->playedRouletteDefaultOutcomeSum);

        $baccaratHold = $instance->BaccaratHold; //< 0 ? abs($instance->BaccaratHold) : -$instance->BaccaratHold;
        $rouletteHold = $instance->RouletteHold; //< 0 ? abs($instance->RouletteHold) : -$instance->RouletteHold;
        $totalHold    = $instance->TotalHold;    //< 0 ? abs($instance->TotalHold)    : -$instance->TotalHold;
        ?>

		<div class=''>
			<h4><B>@lang("admin/{$table}.main_statistic.title")</B></h4>
			<p>@lang("admin/{$table}.main_statistic.TotalAmountOfVisits"): <b>{{ $instance->userAuthorizations->count() }}</b></p>
			<p>@lang("admin/{$table}.main_statistic.TotalGamingDuration"): <b>{{ $totalGamingDuration }} @lang("admin/{$table}.main_statistic.hours")</b></p>
			<p>@lang("admin/{$table}.main_statistic.TotalSumOfDeposits"): <b>{{ $totalAmountSumOfDeposits }} / {{ $totalDefaultAmountSumOfDeposits }}</b></p>
			<p>@lang("admin/{$table}.main_statistic.TotalSumOfWithdrawals"):
				<b>{{ $totalAmountSumOfWithdrawals }} / {{ $totalDefaultAmountSumOfWithdrawals }}</b>
			</p>
			{{--			<p><span class='badge'>?</span>@lang("admin/{$table}.main_statistic.Charge"): <b></b></p>--}}
			<p>@lang("admin/{$table}.main_statistic.DifferenceDepWith"): <b>{{ $totalAmountDifference }} / {{ $totalDefaultAmountDifference }}</b></p>
			<p>@lang("admin/{$table}.main_statistic.BonusesAmount"): <b>{{ $userBonusesAmount }} / {{ $userBonusesDefaultAmount }}</b></p>
			<p>@lang("admin/{$table}.main_statistic.UsedBonusesAmount"): <b>{{ $usedUserBonusesAmount }} / {{ $usedUserBonusesDefaultAmount }}</b></p>
			{{--			<p><span class='badge'>?</span>@lang("admin/{$table}.main_statistic.DiscountAmount"): <b></b></p>--}}
			<p>@lang("admin/{$table}.main_statistic.UserStatus"): <b>{{ $userStatus }}</b></p>
			<p>@lang("admin/{$table}.main_statistic.DateRegistered"): <b>{{ $instance->created_at }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.LastBetDate"): <b>{{ $lastBetDate }}</b></p>

            <hr/>
            <h4>@lang("admin/{$table}.main_statistic.Baccarat")</h4>
            <p>@lang("admin/{$table}.main_statistic.BaccaratTotalBetsAmount"): <b>{{ $allBaccaratPlayedBetsAmountSum }} / {{ $allBaccaratPlayedBetsDefaultAmountSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.BaccaratOutcome"): <b>{{ $allBaccaratPlayedBetsOutcomeSum }} / {{ $allBaccaratPlayedBetsDefaultOutcomeSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.BaccaratHold"): <b>{{ $baccaratHold }}%</b></p>
            <p>@lang("admin/{$table}.main_statistic.BaccaratWinningsAmount"): <b>{{ $allBaccaratWinningSum }} / {{ $allBaccaratDefaultWinningSum }}</b></p>

            <hr/>
            <h4>@lang("admin/{$table}.main_statistic.Roulette")</h4>
            <p>@lang("admin/{$table}.main_statistic.RouletteTotalBetsAmount"): <b>{{ $allRoulettePlayedBetsAmountSum }} / {{ $allRoulettePlayedBetsDefaultAmountSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.RouletteOutcome"): <b>{{ $allRoulettePlayedBetsOutcomeSum }} / {{ $allRoulettePlayedBetsDefaultOutcomeSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.RouletteHold"): <b>{{ $rouletteHold }}%</b></p>
            <p>@lang("admin/{$table}.main_statistic.RouletteWinningsAmount"): <b>{{ $allRouletteWinningSum }} / {{ $allRouletteDefaultWinningSum }}</b></p>

            <hr/>
            <h4>@lang("admin/{$table}.main_statistic.GeneralBettingsData")</h4>
            <p>@lang("admin/{$table}.main_statistic.TotalBetsAmount"): <b>{{ $allTotalPlayedBetsAmountSum }} / {{ $allTotalPlayedBetsDefaultAmountSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.TotalOutcome"): <b>{{ $allTotalPlayedBetsDefaultOutcomeSum }} / {{ $allTotalPlayedBetsDefaultOutcomeSum }}</b></p>
            <p>@lang("admin/{$table}.main_statistic.TotalHold"): <b>{{ $totalHold }}%</b></p>
            <p>@lang("admin/{$table}.main_statistic.TotalWinnings"): <b>{{ $allWinningSum }} / {{ $allDefaultWinningSum }}</b></p>

            <hr/>

            <p>@lang("admin/{$table}.main_statistic.Balance"): <b>{{ $userBalance }} / {{ $userDeafultBalance }}</b></p>
        </div>

        <?php
            $table = 'users';
            $instance = $User::findOrFail($id);

            $maxDailyBet =
                $Operation::from('operations as op')->where('user_till_id', $instance->MoneyTill->id)->whereIn('operatable_type', ['baccarat_bet', 'roulette_bet'])
                        ->selectRaw("SUM(IF(op.amount < 0, op.amount, 0)) AS `amount`")->selectRaw("DATE_FORMAT(op.created_at,'%Y-%m-%d') AS `Date`")
                        ->groupBy('Date')->orderBy('amount', 'asc')->first();
            $maxDailyBetAmount = $maxDailyBet ? $maxDailyBet->amount : 0;
            $maxDailyBetFormattedAmount = BaseModel::formatCurrency($instance->currency_id, $maxDailyBetAmount, true);
            $maxDailyBetDefaultAmount = BaseModel::convertToDefaultCurrencyAndFormat($instance->currency_id, $maxDailyBetAmount, true);
            $maxSingleBet =
                $Operation::from('operations as op')->where('user_till_id', $instance->MoneyTill->id)->whereIn('operatable_type', ['baccarat_bet', 'roulette_bet'])
                        ->min('amount');
            $maxSingleBetFormattedAmount = BaseModel::formatCurrency($instance->currency_id, $maxSingleBet, true);
            $maxSingleBetDefaultAmount = BaseModel::convertToDefaultCurrencyAndFormat($instance->currency_id, $maxSingleBet, true);

            $maxDailyCasinoBetAmount =
                $Operation::from('operations as op')->whereIn('operatable_type', ['baccarat_bet', 'roulette_bet'])
                        ->join('user_tills as ut', function ($j) {
                            $j->on("ut.id", '=', "op.user_till_id");
                        })
                        ->join('exchange_rates as er', function ($j) {
                            $j->on("er.currency_id", '=', "ut.currency_id")->where('er.status', 'active');
                        })
                        ->selectRaw("SUM(IF(op.amount < 0, op.amount / er.rate, 0)) AS `amount`")->selectRaw("DATE_FORMAT(op.created_at,'%Y-%m-%d') AS `Date`")
                        ->groupBy('Date')->orderBy('amount', 'asc')->first()->amount;
            $maxDailyCasinoBetFormattedAmount = BaseModel::formatCurrency(1, $maxDailyCasinoBetAmount, true);
            $maxSingleCasinoBet =
                $Operation::from('operations as op')->whereIn('operatable_type', ['baccarat_bet', 'roulette_bet'])
                        ->join('user_tills as ut', function ($j) {
                            $j->on("ut.id", '=', "op.user_till_id");
                        })
                        ->join('exchange_rates as er', function ($j) {
                            $j->on("er.currency_id", '=', "ut.currency_id")->where('er.status', 'active');
                        })
                        ->selectRaw("IF(op.amount < 0, op.amount / er.rate, 0) AS `amount`")
                        ->min('amount');
            $maxSingleCasinoBetFormattedAmount = BaseModel::formatCurrency(1, $maxSingleCasinoBet, true);
        ?>

        <hr/>

        <div class=''>
            <h4>@lang("admin/{$table}.social_prefs.title")</h4>
            <p>@lang("admin/{$table}.social_prefs.MaxDailyBetAmount"): <b>{{ $maxDailyBetFormattedAmount }} / {{ $maxDailyBetDefaultAmount }}</b></p>
                <p>@lang("admin/{$table}.social_prefs.MaxOneBetAmount"): <b>{{ $maxSingleBetFormattedAmount }} / {{ $maxSingleBetDefaultAmount }}</b></p>
                <br/>
                <p>@lang("admin/{$table}.social_prefs.CasinoMaximalDailyBet"): <b>{{ $maxDailyCasinoBetFormattedAmount }}</b></p>
                <p>@lang("admin/{$table}.social_prefs.CasinoMaximalSingleBet"): <b>{{ $maxSingleCasinoBetFormattedAmount }}</b></p>
        </div>
	@endif

	@if($type === 'user_game_history')
        <?php
        $table = 'users';
        $instance = $User::findOrFail($id);

        $allBaccaratBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->AllBaccaratBetsAmount);
        $allBaccaratBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->AllBaccaratBetsDefaultAmount);
        $wonBaccaratBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->WonBaccaratBetsOutcome);
        $wonBaccaratBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->WonBaccaratBetsDefaultOutcome);

        $allRouletteBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->AllRouletteBetsAmount);
        $allRouletteBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->AllRouletteBetsDefaultAmount);
        $wonRouletteBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->WonRouletteBetsOutcome);
        $wonRouletteBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->WonRouletteBetsDefaultOutcome);

        ///////////

        $allBaccaratPlayedBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratAmountSum);
        $allBaccaratPlayedBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultAmountSum);

        $allBaccaratPlayedBetsOutcomeSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedBaccaratOutcomeSum);
        $allBaccaratPlayedBetsDefaultOutcomeSum = BaseModel::exchangeCurrency($instance->playedBaccaratDefaultOutcomeSum);

        $baccaratHold = $instance->BaccaratHold;

        $playedBaccaratWinningSum        = $instance->playedBaccaratAmountSum - $instance->playedBaccaratOutcomeSum;
        $playedBaccaratDefaultWinningSum = $instance->playedBaccaratDefaultAmountSum - $instance->playedBaccaratDefaultOutcomeSum;

        $inversePlayedBaccaratWinningSum        = $playedBaccaratWinningSum < 0 ? abs($playedBaccaratWinningSum) : -$playedBaccaratWinningSum;
        $inversePlayedBaccaratDefaultWinningSum = $playedBaccaratDefaultWinningSum < 0 ? abs($playedBaccaratDefaultWinningSum) : -$playedBaccaratDefaultWinningSum;


        $allBaccaratWinningSum        = BaseModel::formatCurrency($instance->currency_id, $inversePlayedBaccaratWinningSum);
        $allBaccaratDefaultWinningSum = BaseModel::exchangeCurrency($inversePlayedBaccaratDefaultWinningSum);

        //////////

        $allRoulettePlayedBetsAmountSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedRouletteAmountSum);
        $allRoulettePlayedBetsDefaultAmountSum = BaseModel::exchangeCurrency($instance->playedRouletteDefaultAmountSum);

        $allRoulettePlayedBetsOutcomeSum = BaseModel::formatCurrency($instance->currency_id, $instance->playedRouletteOutcomeSum);
        $allRoulettePlayedBetsDefaultOutcomeSum = BaseModel::exchangeCurrency($instance->playedRouletteDefaultOutcomeSum);

        $rouletteHold = $instance->RouletteHold;

        $playedRouletteWinningSum       = $instance->playedRouletteAmountSum - $instance->playedRouletteOutcomeSum;
        $playedRouetteDefaultWinningSum = $instance->playedRouletteDefaultAmountSum - $instance->playedRouletteDefaultOutcomeSum;

        $inversePlayedRouletteWinningSum       = $playedRouletteWinningSum < 0 ? abs($playedRouletteWinningSum) : -$playedRouletteWinningSum;
        $inversePlayedRouetteDefaultWinningSum = $playedRouetteDefaultWinningSum < 0 ? abs($playedRouetteDefaultWinningSum) : -$playedRouetteDefaultWinningSum;

        $allRouletteWinningSum        = BaseModel::formatCurrency($instance->currency_id, $inversePlayedRouletteWinningSum);
        $allRouletteDefaultWinningSum = BaseModel::exchangeCurrency(($inversePlayedRouetteDefaultWinningSum + $playedRouetteDefaultWinningSum));
        ?>

		<div class=''>
			<h4><B>@lang("admin/{$table}.game_history.title")</B></h4>
			<div class='col-md-6'>
				<p>@lang("admin/{$table}.game_history.Game"): <b>@lang("admin/{$table}.game_history.Baccarat")</b></p>
				<p>@lang("admin/{$table}.game_history.TotalBetsAmount"): <b>{{ $allBaccaratPlayedBetsAmountSum }} / {{ $allBaccaratPlayedBetsDefaultAmountSum }}</b></p>
				<p>@lang("admin/{$table}.game_history.Outcome"): <b>{{ $allBaccaratPlayedBetsOutcomeSum }} / {{ $allBaccaratPlayedBetsDefaultOutcomeSum }}</b></p>
                <p>@lang("admin/{$table}.game_history.Hold"): <b>{{ $baccaratHold }}%</b></p>
                <p>@lang("admin/{$table}.game_history.WinningsAmount"): <b>{{ $allBaccaratWinningSum }} / {{ $allBaccaratDefaultWinningSum }}</b></p>
				{{--<p><span class='badge'>?</span>@lang("admin/{$table}.game_history.TotalBetsBonuses"): <b></b></p>--}}
				{{--<p><span class='badge'>?</span>@lang("admin/{$table}.game_history.TotalWinningsBonuses"): <b></b></p>--}}
			</div>
			<div class='col-md-6'>
				<p>@lang("admin/{$table}.game_history.Game"): <b>@lang("admin/{$table}.game_history.Roulette")</b></p>
				<p>@lang("admin/{$table}.game_history.TotalBetsAmount"): <b>{{ $allRoulettePlayedBetsAmountSum }} / {{ $allRoulettePlayedBetsDefaultAmountSum }}</b></p>
				<p>@lang("admin/{$table}.game_history.Outcome"): <b>{{ $allRoulettePlayedBetsOutcomeSum }} / {{ $allRoulettePlayedBetsDefaultOutcomeSum }}</b></p>
                <p>@lang("admin/{$table}.game_history.Hold"): <b>{{ $rouletteHold }}%</b></p>
                <p>@lang("admin/{$table}.game_history.WinningsAmount"): <b>{{ $allRouletteWinningSum }} / {{ $allRouletteDefaultWinningSum }}</b></p>
				{{--<p><span class='badge'>?</span>@lang("admin/{$table}.game_history.TotalBetsBonuses"): <b></b></p>--}}
				{{--<p><span class='badge'>?</span>@lang("admin/{$table}.game_history.TotalWinningsBonuses"): <b></b></p>--}}
			</div>
		</div>
	@endif

	@if($type === 'bets_bank_accruals')
        <?php
        $table = 'users';
        $instance = $User::findOrFail($id);

        $betsAmountBonus = \App\Models\Bonus::where('name', 'bets_amount_bonus')->first();

        $lastBonus = $UserBonus::select('applied_at')
            ->where(['bonus_id' => $betsAmountBonus->id, 'user_id' => $instance->id, 'status' => 'applied'])
            ->orderBy('applied_at', 'desc')
            ->first();

        $totalBonusAmount = $UserBonus::where(['user_id' => $instance->id, 'bonus_id' => $betsAmountBonus->id, 'status' => 'applied'])
            ->sum('amount');

        [$totalBetsAmount, $totalBetsBankAmount] = \Admin\Custom\BetsBankEvaluator::collectRoundStats($instance->id, [
            \DragonStudio\BonusProgram\BonusHelper::getLastUserBonusApplicationDate($betsAmountBonus->id, $instance->id),
            now()
        ]);

        $cashbackPercent = $betsAmountBonus->bonus_amount_percent;

        $rewardAmount = $totalBetsBankAmount / 100 * $cashbackPercent;

        [$totalBonusAmountF, $totalBonusAmountDF] = BaseModel::getFormattedAndConvertedExchanged($instance->currency_id, $totalBonusAmount);
        [$totalBetsAmountF, $totalBetsAmountDF] = BaseModel::getFormattedAndConvertedExchanged($instance->currency_id, $totalBetsAmount);
        [$totalBetsBankAmountF, $totalBetsBankAmountDF] = BaseModel::getFormattedAndConvertedExchanged($instance->currency_id, $totalBetsBankAmount);
        [$rewardAmountF, $rewardAmountDF] = BaseModel::getFormattedAndConvertedExchanged($instance->currency_id, $rewardAmount);

        ?>

        <div class=''>
            <h4><B>@lang("admin/{$table}.bets_bank_accruals.title")</B></h4>
            <div class='col-md-6'>
                <p>@lang("admin/{$table}.bets_bank_accruals.TotalBonusAmount"): <b>{{ $totalBonusAmountF }} / {{ $totalBonusAmountDF }}</b></p>
                <p>@lang("admin/{$table}.bets_bank_accruals.RecentBonusTransferDate"): <b>{{ $lastBonus ? $lastBonus->applied_at->format('d.m.Y H:i') : __('admin/common.na') }}</b></p>
                <p>@lang("admin/{$table}.bets_bank_accruals.CurrentBetsAmount"): <b>{{ $totalBetsAmountF }} / {{ $totalBetsAmountDF }}</b></p>
                <p>@lang("admin/{$table}.bets_bank_accruals.CurrentBetsBankAmount"): <b>{{ $totalBetsBankAmountF }} / {{ $totalBetsBankAmountDF }}</b></p>
                <p>@lang("admin/{$table}.bets_bank_accruals.CashbackPercent"): <b>{{ $cashbackPercent }}%</b></p>
                <p>@lang("admin/{$table}.bets_bank_accruals.BonusAmountToBeTransferred"): <b>{{ $rewardAmountF }} / {{ $rewardAmountDF }}</b></p>
            </div>
        </div>
	@endif
</div>