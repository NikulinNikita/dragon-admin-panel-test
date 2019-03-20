<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['exportOnly']])
@endif

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table">
	<tr>
		<th style="width: 20px !important;">#</th>
		<th>@lang("admin/{$page}.Number")</th>
		<th>@lang("admin/{$page}.DepositsAmount")</th>
		<th>@lang("admin/{$page}.WithdrawalsAmount")</th>
		<th>@lang("admin/{$page}.BalanceAlter")</th>
	</tr>
	@foreach($groupedBankAccounts as $currency_id => $bankAccounts)
		<tr class="info">
			<td></td>
			<td><b>{{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		@foreach($bankAccounts as $bankAccount)
			<tr>
				<td>{{ $bankAccount->id }}</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number,
							'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">{{ $bankAccount->number }}
					</a>
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number, 'operatableTypes' => ['deposit_request'],
					'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->deposits_amount) }}
					</a>
				</td>
				<td>
					<a href="{{ \BaseModel::generateUrl('BankAccountOperation', ['number' => $bankAccount->number, 'operatableTypes' => ['withdrawal_request'],
					'date_from' => $dateFrom, 'date_to' => $dateTo]) }}">
						{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccount->withdrawals_amount) }}
					</a>
				</td>
				<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccount->balance_alter) }}</td>
			</tr>
		@endforeach
		<tr class="danger">
			<td></td>
			<td><b>@lang("admin/{$page}.SumOf") {{ session()->get("admin.currencies.{$currency_id}.code") }}</b></td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('deposits_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, -$bankAccounts->sum('withdrawals_amount')) }}</td>
			<td>{{ BaseModel::formatCurrency($bankAccount->currency_id, $bankAccounts->sum('balance_alter')) }}</td>
		</tr>
	@endforeach
</table>

<?php //Meta::loadPackage(['AjaxResponse']) ?>