<h3 class="text-center">@lang("admin/dashboard.NewUsersActivity")</h3>
<div class="row">
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-blue"><i class="fa fa-user-plus"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.Users")</span>
				<span class="info-box-number">@lang("admin/dashboard.RegisteredToday"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayRegisteredUsersCount, 'link' => $todayRegisteredUsersUrl])
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-green"><i class="fa fa-plus-circle"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.DepositRequests")</span>
				<span class="info-box-number">@lang("admin/dashboard.Requests"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayRegisteredUsersDepositRequestsCount, 'link' => $todayRegisteredUsersDepositRequestsUrl])
				</span>
				<span class="info-box-number">@lang("admin/dashboard.TotalSum"):
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayRegisteredUsersDepositRequestsAmount),
					'link' => $todayRegisteredUsersDepositRequestsUrl, 'cond' => $todayRegisteredUsersDepositRequestsAmount])
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-red"><i class="fa fa-minus-circle"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.WithdrawalRequests")</span>
				<span class="info-box-number">@lang("admin/dashboard.Requests"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayRegisteredUsersWithdrawalRequestsCount, 'link' => $todayRegisteredUsersWithdrawalRequestsUrl])
				</span>
				<span class="info-box-number">@lang("admin/dashboard.TotalSum"):
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayRegisteredUsersWithdrawalRequestsAmount),
					'link' => $todayRegisteredUsersWithdrawalRequestsUrl, 'cond' => $todayRegisteredUsersWithdrawalRequestsAmount])
				</span>
			</div>
		</div>
	</div>
</div>

<h3 class="text-center">@lang("admin/dashboard.TotalUsersActivity")</h3>
<div class="row">
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-blue"><i class="fa fa-user-plus"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.Users")</span>
				<span class="info-box-number">@lang("admin/dashboard.ActiveToday"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayActiveUsersCount, 'link' => $todayActiveUsersUrl])
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-green"><i class="fa fa-plus-circle"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.DepositRequests")</span>
				<span class="info-box-number">@lang("admin/dashboard.Requests"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayAllUsersDepositRequestsCount, 'link' => $todayAllUsersDepositRequestsUrl])
				</span>
				<span class="info-box-number">@lang("admin/dashboard.TotalSum"):
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllUsersDepositRequestsAmount),
					'link' => $todayAllUsersDepositRequestsUrl, 'cond' => $todayAllUsersDepositRequestsAmount])
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-red"><i class="fa fa-minus-circle"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.WithdrawalRequests")</span>
				<span class="info-box-number">@lang("admin/dashboard.Requests"):
					@include('admin::pages.mini.link_or_text', ['var' => $todayAllUsersWithdrawalRequestsCount, 'link' => $todayAllUsersWithdrawalRequestsUrl])
				</span>
				<span class="info-box-number">@lang("admin/dashboard.TotalSum"):
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllUsersWithdrawalRequestsAmount),
					'link' => $todayAllUsersWithdrawalRequestsUrl, 'cond' => $todayAllUsersWithdrawalRequestsAmount])
				</span>
			</div>
		</div>
	</div>
</div>

<h3 class="text-center">@lang("admin/dashboard.TotalUsersBets")</h3>
<div class="row">
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-blue"><i class="fa fa-money"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.Bets")</span>
				{{-- <span class="info-box-number">
					@lang("admin/dashboard.B")( @include('admin::pages.mini.link_or_text', ['var' => $todayAllBaccaratBetsCount, 'link' => $todayAllBaccaratBetsUrl]) ) +
					@lang("admin/dashboard.R")( @include('admin::pages.mini.link_or_text', ['var' => $todayAllRouletteBetsCount, 'link' => $todayAllRouletteBetsUrl]) ) =
					{{ $todayAllBaccaratBetsCount + $todayAllRouletteBetsCount }}
				</span> --}}
				<span class="info-box-number">
					@lang("admin/dashboard.Baccarat"): @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllBaccaratBetsSum),
					'link' => $todayAllBaccaratBetsUrl, 'cond' => $todayAllBaccaratBetsSum])
				</span>
				<span class="info-box-number">
					@lang("admin/dashboard.Roulette"): @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllRouletteBetsSum),
					'link' => $todayAllRouletteBetsUrl, 'cond' => $todayAllRouletteBetsSum])
				</span>
				<span class="info-box-number">
						@lang("admin/dashboard.Total"): {{\BaseModel::exchangeCurrency($todayAllBaccaratBetsSum + $todayAllRouletteBetsSum)}}
				</span>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.Outcome")</span>
				{{-- <span class="info-box-number">
					@lang("admin/dashboard.B")( @include('admin::pages.mini.link_or_text', ['var' => $todayWonBaccaratBetsCount, 'link' => $todayWonBaccaratBetsUrl]) ) +
					@lang("admin/dashboard.R")( @include('admin::pages.mini.link_or_text', ['var' => $todayWonRouletteBetsCount, 'link' => $todayWonRouletteBetsUrl]) ) =
					{{ $todayWonBaccaratBetsCount + $todayWonRouletteBetsCount }}
				</span> --}}
				<span class="info-box-number">
					@lang("admin/dashboard.Baccarat"): @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayWonBaccaratOutcomeSum),
					'link' => $todayWonBaccaratBetsUrl, 'cond' => $todayWonBaccaratOutcomeSum])
				</span>
				<span class="info-box-number">
					@lang("admin/dashboard.Roulette"): @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayWonRouletteOutcomeSum),
					'link' => $todayWonRouletteBetsUrl, 'cond' => $todayWonRouletteOutcomeSum])
				</span>
				<span class="info-box-number">
					@lang("admin/dashboard.Total"): {{ \BaseModel::exchangeCurrency($todayWonBaccaratOutcomeSum + $todayWonRouletteOutcomeSum) }}
				</span>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-red"><i class="fa fa-money"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.Result")</span>
				{{-- <span class="info-box-number">
					@lang("admin/dashboard.B")( @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllBaccaratProfitability), 'link' => false]) ) +
					@lang("admin/dashboard.R")( @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllRouletteProfitability), 'link' => false]) )  =
					{{ \BaseModel::exchangeCurrency($todayAllBaccaratProfitability + $todayAllRouletteProfitability) }}
				</span> --}}
				<span class="info-box-number">
					@lang("admin/dashboard.Baccarat"):  
					
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum),
						'link' => $todayAllBaccaratBetsUrl, 'cond' => ($todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum) != 0])
					
					(@include('admin::pages.mini.link_or_text', ['var' => $todayAllBaccaratProfitabilityPercents, 'link' => false])%)
				</span>	
				<span class="info-box-number">
					@lang("admin/dashboard.Roulette"):

					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency(($todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum)),
						'link' => $todayAllRouletteBetsUrl, 'cond' => ($todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum) != 0])

					(@include('admin::pages.mini.link_or_text', ['var' => $todayAllRouletteProfitabilityPercents, 'link' => false])%)
				</span>
				<span class="info-box-number">
					@lang("admin/dashboard.TwoGames"):
					
					@include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency((($todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum) + ($todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum))),
						'link' => false, 'cond' => false]) 

					(@include('admin::pages.mini.link_or_text', ['var' => $todayTotalProfitabilityPercents, 'link' => false])%)
				</span>
			</div>
		</div>
	</div>
</div>

{{-- <div class="row">
	<div class="col-md-4 col-sm-6 col-xs-12">
		<div class="info-box">
			<span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
			<div class="info-box-content">
				<span class="info-box-text">@lang("admin/dashboard.WonBets")</span>
				<span class="info-box-number">
					@lang("admin/dashboard.B")( @include('admin::pages.mini.link_or_text', ['var' => $todayWonBaccaratBetsCount, 'link' => $todayWonBaccaratBetsUrl]) ) +
					@lang("admin/dashboard.R")( @include('admin::pages.mini.link_or_text', ['var' => $todayWonRouletteBetsCount, 'link' => $todayWonRouletteBetsUrl]) ) =
					{{ $todayWonBaccaratBetsCount + $todayWonRouletteBetsCount }}
				</span>
				<span class="info-box-number">
					@lang("admin/dashboard.B")( @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayWonBaccaratBetsSum),
					'link' => $todayWonBaccaratBetsUrl, 'cond' => $todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum]) ) +
					@lang("admin/dashboard.R")( @include('admin::pages.mini.link_or_text', ['var' => \BaseModel::exchangeCurrency($todayWonRouletteBetsSum),
					'link' => $todayWonRouletteBetsUrl, 'cond' => $todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum]) )  =
					{{ \BaseModel::exchangeCurrency(($todayAllBaccaratBetsSum - $todayAllBaccaratOutcomeSum) + ($todayAllRouletteBetsSum - $todayAllRouletteOutcomeSum)) }}
				</span>
			</div>
		</div>
	</div>
</div> --}}

<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">@lang("admin/dashboard.CommonChart")</h3>
				<div class="box-tools">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body">
				<div class="pull-right">
					<div class="col-md-3">
						<div class="form-group">
							<label for="select_date_period">@lang("admin/common.SelectType")</label>
							<select name="select_type" id="select_type" class="form-control select_type">
								<option value="registeredUsersCount">@lang("admin/dashboard.RegisteredUsers")</option>
								<option value="DepositRequestsCount">@lang("admin/dashboard.DepositRequestsCount")</option>
								<option value="DepositRequestsAmount">@lang("admin/dashboard.DepositRequestsAmount")</option>
								<option value="AllBaccaratBetsAmount">@lang("admin/dashboard.AllBaccaratBetsAmount")</option>
								<option value="WonBaccaratBetsAmount">@lang("admin/dashboard.WonBaccaratBetsAmount")</option>
								<option value="AllBaccaratProfitabilityPercents">@lang("admin/dashboard.AllBaccaratProfitabilityInPercents")</option>
								<option value="AllRouletteBetsAmount">@lang("admin/dashboard.AllRouletteBetsAmount")</option>
								<option value="WonRouletteBetsAmount">@lang("admin/dashboard.WonRouletteBetsAmount")</option>
								<option value="AllRouletteProfitabilityPercents">@lang("admin/dashboard.AllRouletteProfitabilityInPercents")</option>
							</select>
						</div>
					</div>
					<div class="col-md-3">
						@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_from', 'value' => \Carbon\Carbon::today()->subMonth(),
						'label' => __("admin/common.DateFrom"), 'date' => true])
					</div>
					<div class="col-md-3">
						@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_to', 'value' => \Carbon\Carbon::today(),
						'label' => __("admin/common.DateTo"), 'date' => true])
					</div>
					<div class="col-md-3">
						<button type="button" class="btn btn-primary ajax_send" style="margin-top: 24px">@lang("admin/common.Filter")</button>
					</div>
				</div>
				<div class="chart"></div>
			</div>
		</div>
	</div>
</div>

<?php Meta::loadPackage(['Chart']) ?>