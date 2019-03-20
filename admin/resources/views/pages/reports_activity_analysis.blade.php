<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['dateRange']])
@endif

<div class="col-md-6">
	<h4>@lang("admin/{$page}.BaccaratBets")</h4>
	<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
		<tr>
			<th>@lang("admin/{$page}.BetType")</th>
			<th>@lang("admin/{$page}.BetAmount")</th>
			<th>%</th>
		</tr>
		@foreach($baccaratBetTypes as $baccaratBetType)
			<tr>
				<td>{{ $baccaratBetType }}</td>
				<td>{{ BaseModel::exchangeCurrency($baccaratBets->sum($baccaratBetType)) }}</td>
				<td>{{ $baccaratBets->sum('total_amount') ? round($baccaratBets->sum($baccaratBetType) * 100 / $baccaratBets->sum('total_amount')) : 0 }}%</td>
			</tr>
		@endforeach
		<tr class="danger">
			<th>@lang("admin/common.Total")</th>
			<th>{{ BaseModel::exchangeCurrency($baccaratBets->sum('total_amount')) }}</th>
			<th>100%</th>
		</tr>
	</table>
</div>

<div class="col-md-6">
	<h4>@lang("admin/{$page}.RouletteBets")</h4>
	<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
		<tr>
			<th>@lang("admin/{$page}.BetType")</th>
			<th>@lang("admin/{$page}.BetAmount")</th>
			<th>%</th>
		</tr>
		@foreach($rouletteBetTypes as $rouletteBetType)
			<tr>
				<td>{{ $rouletteBetType }}</td>
				<td>{{ BaseModel::exchangeCurrency($rouletteBets->sum($rouletteBetType)) }}</td>
				<td>{{ $rouletteBets->sum('total_amount') ? round($rouletteBets->sum($rouletteBetType) * 100 / $rouletteBets->sum('total_amount')) : 0 }}%</td>
			</tr>
		@endforeach
		@for ($i = 0; $i <= 36; $i++)
			<tr>
				<td>@lang("admin/{$page}.Number") {{ "{$i}" }}</td>
				<td>{{ BaseModel::exchangeCurrency($rouletteBets->sum("n{$i}")) }}</td>
				<td>{{ $rouletteBets->sum('total_amount') ? round($rouletteBets->sum("n{$i}") * 100 / $rouletteBets->sum('total_amount')) : 0 }}%</td>
			</tr>
		@endfor
		<tr class="danger">
			<th>@lang("admin/common.Total")</th>
			<th>{{ BaseModel::exchangeCurrency($rouletteBets->sum('total_amount')) }}</th>
			<th>100%</th>
		</tr>
	</table>
</div>

<?php //Meta::loadPackage(['AjaxResponse']) ?>