<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['dateRange']])
@endif

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
	<tr>
		<th>@lang("admin/{$page}.Type")</th>
		<th>@lang("admin/{$page}.Count")</th>
		<th>@lang("admin/{$page}.Amount")</th>
		<th>@lang("admin/{$page}.Min")</th>
		<th>@lang("admin/{$page}.Max")</th>
		<th>@lang("admin/{$page}.Avg")</th>
	</tr>
	@foreach($requests as $req)
		<tr>
			<td>@lang("admin/{$page}.{$req->type}")</td>
			<td>
				<a href="{{ \BaseModel::generateUrl($req->type, ['date_from' => $dateFrom, 'date_to' => $dateTo, 'statuses' => ['succeed']]) }}">{{ $req->count }}</a>
			</td>
			<td>{{ BaseModel::exchangeCurrency($req->amount) }}</td>
			<td>{{ BaseModel::exchangeCurrency($req->min) }}</td>
			<td>{{ BaseModel::exchangeCurrency($req->max) }}</td>
			<td>{{ BaseModel::exchangeCurrency($req->avg) }}</td>
		</tr>
	@endforeach
	<tr class="danger">
		<th>@lang("admin/common.Total")</th>
		<th></th>
		<th>{{ BaseModel::exchangeCurrency($requests->first()->amount -  $requests->last()->amount) }}</th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
</table>

<?php //Meta::loadPackage(['AjaxResponse']) ?>