@php
	$totalSum = 0;
	$totalCount  = 0;
@endphp

<h2 class="text-left">@lang("admin/{$page}.BonusesReports")</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['users', 'dateRange', 'noExport']])
@endif

<div class="col-md-6">
	<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
		<tr>
			<th>@lang("admin/{$page}.BonusName")</th>
			<th>@lang("admin/{$page}.BonusCount")</th>
			<th>@lang("admin/{$page}.BonusSum")</th>
		</tr>
		@foreach($bonuses as $bonus)
			@php
				$totalSum += $bonus->sum;
				$totalCount  += $bonus->count;
			@endphp
			<tr>
				<td>{{ $bonus->title }}</td>
				<td>
					@if ($user && $bonus->count)
						<a href="{{ \BaseModel::generateUrl('UserBonus', array_merge(request()->only('user_id', 'date_from', 'date_to'), ['bonus_id' => $bonus->id])) }}">
							@endif
							{{ $bonus->count }}
							@if ($user && $bonus->count)
						</a>
					@endif
				</td>
				<td>
					@if ($user)
						{{ BaseModel::formatCurrency($user->currency_id, $bonus->sum) }}
					@else
						{{ BaseModel::formatCurrency(1, $bonus->sum) }}
					@endif
				</td>
			</tr>
		@endforeach
		<tr class="danger">
			<th>@lang("admin/common.Total")</th>
			<th>{{ $totalCount }}</th>
			<th>
				@if ($user)
					{{ BaseModel::formatCurrency($user->currency_id, $totalSum) }}
				@else
					{{ BaseModel::formatCurrency(1, $totalSum) }}
				@endif
			</th>
		</tr>
	</table>
</div>