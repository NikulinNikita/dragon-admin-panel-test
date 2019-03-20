<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['dateRange', 'currencySelect', 'valuesRange']])
@endif

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
	<tr>
		<th>@lang("admin/{$page}.Parameters")</th>
		<th>@lang("admin/{$page}.Baccarat")</th>
		<th>@lang("admin/{$page}.Roulette")</th>
		<th>@lang("admin/common.Total")</th>
	</tr>
	@if($groupedData && count($groupedData->getAttributes()))
		@foreach(['count', 'amount', 'outcome', 'result', 'min', 'max', 'avg'] as $groupedType)
            <?php
            $currencyId = $currencyId ?? 1;
            $groupedBaccaratBets = $groupedData->{"baccarat_bets_{$groupedType}"};
            $groupedRouletteBets = $groupedData->{"roulette_bets_{$groupedType}"};
            $groupedTotalBets = $groupedData->{"baccarat_bets_{$groupedType}"} + $groupedData->{"roulette_bets_{$groupedType}"};
            $totalBetsAmount = $groupedData->baccarat_bets_amount + $groupedData->roulette_bets_amount;
            $totalBetsCount = $groupedData->baccarat_bets_count + $groupedData->roulette_bets_count;
            ?>

			<tr>
				<td>@lang("admin/{$page}.Bets{$groupedType}")</td>
				<td>{{ $groupedType !== 'count' ? BaseModel::convertDefaultCurrencyAndFormat($currencyId, $groupedBaccaratBets) : $groupedBaccaratBets }}</td>
				<td>{{ $groupedType !== 'count' ? BaseModel::convertDefaultCurrencyAndFormat($currencyId, $groupedRouletteBets) : $groupedRouletteBets }}</td>
				@if ($groupedType == 'min' || $groupedType == 'max')
					<td></td>
				@else
					@if ($groupedType == 'avg')
						<td>
							{{ BaseModel::convertDefaultCurrencyAndFormat($currencyId, $totalBetsCount ? ($totalBetsAmount) / ($totalBetsCount) : 0) }}
						</td>
					@else
						<td>{{ $groupedType !== 'count' ? BaseModel::convertDefaultCurrencyAndFormat($currencyId, $groupedTotalBets) : $groupedTotalBets }}</td>
					@endif
				@endif
			</tr>
		@endforeach
		@foreach(['count', 'amount'] as $groupedType)
            <?php $totally = $groupedData->{"baccarat_bets_{$groupedType}"} + $groupedData->{"roulette_bets_{$groupedType}"} ?>
			<tr>
				<td>@lang("admin/{$page}.Bets{$groupedType}weight")</td>
				<td>{{ $totally ? round($groupedData->{"baccarat_bets_{$groupedType}"} * 100 / $totally, 2) : 0 }}%</td>
				<td>{{ $totally ? round($groupedData->{"roulette_bets_{$groupedType}"} * 100 / $totally, 2) : 0 }}%</td>
				<td></td>
			</tr>
		@endforeach
	@endif
</table>

<?php //Meta::loadPackage(['AjaxResponse']) ?>