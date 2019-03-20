<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['dateRange']])
@endif

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table">
	<tr>
		<th>#</th>
		<th>@lang("admin/{$page}.Number")</th>
		<th>@lang("admin/{$page}.BalanceBefore")</th>
		<th>@lang("admin/{$page}.ReceivedDepositAmount")</th>
		<th>@lang("admin/{$page}.ReceivedWithdrawalAmount")</th>
		<th>@lang("admin/{$page}.DepositCommission")</th>
		<th>@lang("admin/{$page}.WithdrawalCommission")</th>
		<th>@lang("admin/{$page}.TotalDepositAmount")</th>
		<th>@lang("admin/{$page}.TotalWithdrawalAmount")</th>
		<th>@lang("admin/{$page}.IODepositAmount")</th>
		<th>@lang("admin/{$page}.IOWithdrawalAmount")</th>
		<th>@lang("admin/{$page}.BalanceAfter")</th>
		<th>@lang("admin/{$page}.BalanceAfterBalanceBefore")</th>
		<th>@lang("admin/{$page}.Weight")</th>
	</tr>
	@foreach($groupedBankAccounts as $currency_id => $bankAccounts)
		<tr class="info">
			<td></td>
			<td></td>
			<td><b>{{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		@foreach($bankAccounts as $bankAccount)
			<tr>
				<td>{{ $bankAccount->id }}</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">{{ $bankAccount->title }}
					</a>
				</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_before) }}</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->received_deposit_amount) }}</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccount->received_withdrawal_amount) }}</td>
				<td>{{ +$bankAccount->total_deposit_amount ? round(($bankAccount->total_deposit_amount - $bankAccount->received_deposit_amount) * 100 / $bankAccount->total_deposit_amount, 2) : 0 }}
					%
				</td>
				<td>{{ +$bankAccount->total_withdrawal_amount ? round(($bankAccount->total_withdrawal_amount - $bankAccount->received_withdrawal_amount) * 100 / $bankAccount->total_withdrawal_amount, 2) : 0 }}
					%
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'operatableTypes' => ['deposit_request'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->total_deposit_amount) }}
					</a>
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'operatableTypes' => ['withdrawal_request'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccount->total_withdrawal_amount) }}
					</a>
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'operatableTypes' => ['internal_operations'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->io_deposit_amount) }}
					</a>
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'operatableTypes' => ['-internal_operations'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->io_withdrawal_amount) }}
					</a>
				</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_after) }}</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_after - $bankAccount->balance_before) }}</td>
				<td>{{ $bankAccounts->sum('balance_after') ? round($bankAccount->balance_after * 100 / $bankAccounts->sum('balance_after'), 2) : 0 }}%</td>
			</tr>
		@endforeach
		<tr class="danger">
			<td></td>
			<td><b>@lang("admin/{$page}.SumOf") {{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_before')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('received_deposit_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccounts->sum('received_withdrawal_amount')) }}</td>
			<td></td>
			<td></td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('total_deposit_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccounts->sum('total_withdrawal_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('io_deposit_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('io_withdrawal_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_after')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_after') - $bankAccounts->sum('balance_before')) }}</td>
			<td>100%</td>
		</tr>
	@endforeach
</table>

<?php //Meta::loadPackage(['AjaxResponse']) ?>