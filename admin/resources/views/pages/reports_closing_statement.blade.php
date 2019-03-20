<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page],
	'components' => [(auth()->user()->isAbleTo(['manage_everything']) || auth()->user()->hasAnyRole(['general', 'accountant'])) ? 'dateRange' : 'exportOnly']])
@endif

<div class="row">
	<div class="col-md-5">
		<h3 class="text-left">@lang("admin/{$page}.CommonInformation")</h3>
		<table class="table table-grey table-hover table-condensed table-responsive b-reports-table">
			<tr>
				<th>@lang("admin/{$page}.Period")</th>
				<td>{{ $dateFrom }} - {{ $dateTo }}</td>
			</tr>
			<tr>
				<th>@lang("admin/{$page}.Days")</th>
				<td>{{ $obj->daysPeriod}}</td>
			</tr>
			<tr>
				<th>@lang("admin/{$page}.ActiveUsers")</th>
				<td>{{ $obj->active_users}}</td>
			</tr>
		</table>
	</div>

	<div class="col-md-2"></div>
	<div class="col-md-4">
		<h3 class="text-left">.</h3>
		<table class="table table-grey table-hover table-condensed table-responsive b-reports-table">
			<tr>
				<th>@lang("admin/{$page}.FinancialResult")</th>
				<th>@lang("admin/{$page}.Balance")</th>
			</tr>
			<tr class="danger">
				<td>{{ BaseModel::formatCurrency(1, -$obj->total_bets_amount->default->total - $obj->used_bonuses_amount->default->total
				 - $obj->used_partners_amount->default->total) }}</td>
				<td>{{ BaseModel::formatCurrency(1, ($allBankAccounts->sum('default_deposit_amount') - $allBankAccounts->sum('default_withdrawal_amount'))
				 - ($obj->deposits_amount->default->total - $obj->withdrawals_amount->default->total)) }}
				</td>
			</tr>
		</table>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3 class="text-left">@lang("admin/{$page}.GameTills")</h3>
        <?php
        $rows = [
            trans("admin/{$page}.BalanceBefore")    => ['balance_before'],
            trans("admin/{$page}.DepositsChanges")  => ['total_deposits_withdrawals_amount'],
            trans("admin/{$page}.BonusesChanges")   => ['used_bonuses_amount'],
            trans("admin/{$page}.PartnersChanges")  => ['used_partners_amount'],
            trans("admin/{$page}.GamesBetsChanges") => ['total_bets_amount'],
            trans("admin/{$page}.BalanceAfter")     => ['total_balance_after'],
        ];
        ?>
		@include('admin::pages.mini.table_with_currencies', ['tableName' => 'GameTills', 'rows' => $rows, 'hiddenColumns' => ['balance_after']])
	</div>
</div>
<hr style="border-top: 1px solid #546d84;">

<div class="row">
	<div class="col-md-10">
		<h3 class="text-left">@lang("admin/{$page}.Deposits")</h3>
        <?php
        $rows = [
            trans("admin/{$page}.BalanceBefore")     => ['deposits_withdrawals_amount_before'],
            trans("admin/{$page}.DepositsAmount")    => ['deposits_amount'],
            trans("admin/{$page}.WithdrawalsAmount") => ['withdrawals_amount'],
            trans("admin/{$page}.BalanceAfter")      => ['deposits_amount_after'],
        ];
        ?>
		@include('admin::pages.mini.table_with_currencies', ['tableName' => 'Deposits', 'rows' => $rows])
	</div>
</div>

<div class="col-md-12">
	<h3 class="text-left">@lang("admin/{$page}.OtherTills")</h3>
    <?php
    $preRows = [
        trans("admin/{$page}.Till") => [trans("admin/{$page}.Bonus"), trans("admin/{$page}.AgentReward")],
    ];
    $rows = [
        trans("admin/{$page}.BalanceBefore") => ['bonuses_balance_before', 'partners_balance_before'],
        trans("admin/{$page}.Added")         => ['bonuses_amount', 'partners_amount'],
        trans("admin/{$page}.Used")          => ['used_bonuses_amount', 'used_partners_amount'],
        trans("admin/{$page}.Canceled")      => ['canceled_bonuses_amount', 'canceled_partners_amount'],
        trans("admin/{$page}.BalanceAfter")  => ['total_bonuses_balance_after', 'total_partners_balance_after'],
    ];
    ?>
	@include('admin::pages.mini.table_with_currencies',
	['tableName' => 'OtherTills', 'preRows' => $preRows, 'rows' => $rows, 'hiddenColumns' => ['bonuses_balance_after', 'partners_balance_after']])
</div>

<div class="col-md-10">
	<h3 class="text-left">@lang("admin/{$page}.Games")</h3>
    <?php
    $preRows = [
        trans("admin/{$page}.Game") => [trans("admin/{$page}.Baccarat"), trans("admin/{$page}.Roulette")],
    ];
    $rows = [
        trans("admin/{$page}.BetsAmount")    => ['baccarat_bets_amount', 'roulette_bets_amount'],
        trans("admin/{$page}.OutcomeAmount") => ['baccarat_bets_outcome', 'roulette_bets_outcome'],
        trans("admin/{$page}.Result")        => ['baccarat_bets_result', 'roulette_bets_result'],
    ];
    ?>
	@include('admin::pages.mini.table_with_currencies', ['tableName' => 'Games', 'preRows' => $preRows, 'rows' => $rows])
</div>

@if(auth()->user()->isAbleTo(['manage_everything']) || auth()->user()->hasRole('accountant'))
	<div class="col-md-12">
		<hr style="border-top: 1px solid #546d84;">
		<h3 class="text-left">@lang("admin/{$page}.BankAccounts")</h3>
		<table class="table table-striped table-hover table-condensed table-responsive b-reports-table">
			<tr>
				<th>@lang("admin/{$page}.Number")</th>
				<th>@lang("admin/{$page}.BalanceBefore")</th>
				<th>@lang("admin/{$page}.ReceivedDepositAmount")</th>
				<th>@lang("admin/{$page}.IODepositAmount")</th>
				<th>@lang("admin/{$page}.ReceivedWithdrawalAmount")</th>
				<th>@lang("admin/{$page}.IOWithdrawalAmount")</th>
				<th>@lang("admin/{$page}.BalanceAfter")</th>
			</tr>
			@foreach($groupedBankAccounts as $currency_id => $bankAccounts)
				<tr class="info">
					<td></td>
					<td><b>{{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				@foreach($bankAccounts as $bankAccount)
					<tr>
						<td>
							<a href="{{ \BaseModel::generateUrl('BankAccountOperation',
							['number' => $bankAccount->number, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
								{{ $bankAccount->title }}
							</a>
						</td>
						<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_before) }}</td>
						<td>
							<a href="{{ \BaseModel::generateUrl('BankAccountOperation',
							['number' => $bankAccount->number, 'operatableTypes' => ['deposit_request'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
								{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->total_deposit_amount) }}
							</a>
						</td>
						<td>
							<a href="{{ \BaseModel::generateUrl('BankAccountOperation',
							['number' => $bankAccount->number, 'operatableTypes' => ['internal_operations'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
								{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->io_deposit_amount) }}
							</a>
						</td>
						<td>
							<a href="{{ \BaseModel::generateUrl('BankAccountOperation',
							['number' => $bankAccount->number, 'operatableTypes' => ['withdrawal_request'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
								{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->total_withdrawal_amount) }}
							</a>
						</td>
						<td>
							<a href="{{ \BaseModel::generateUrl('BankAccountOperation',
							['number' => $bankAccount->number, 'operatableTypes' => ['-internal_operations'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
								{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->io_withdrawal_amount) }}
							</a>
						</td>
						<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_after) }}</td>
					</tr>
				@endforeach
				<tr class="danger">
					<td><b>@lang("admin/{$page}.SumOf") {{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_before')) }}</td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('total_deposit_amount')) }}</td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('io_deposit_amount')) }}</td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccounts->sum('total_withdrawal_amount')) }}</td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccounts->sum('io_withdrawal_amount')) }}</td>
					<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_after')) }}</td>
				</tr>
			@endforeach
			<tr class="danger" style="font-size: 16px; font-weight: 700">
				<td><b>@lang("admin/common.Total"):</b></td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_balance_before')) }}</td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_deposit_amount')) }}</td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_io_deposit_amount')) }}</td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_withdrawal_amount')) }}</td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_io_withdrawal_amount')) }}</td>
				<td>{{ BaseModel::formatCurrency(1, $allBankAccounts->sum('default_balance_after')) }}</td>
			</tr>
		</table>
	</div>
@endif

<?php //Meta::loadPackage(['AjaxResponse']) ?>