<?php
$model      = $model ?? ($alias ?? null);
$buttonType = ! is_array($buttonType) ? [$buttonType] : $buttonType;
$params     = isset($params) ? json_encode($params) : null;
?>

@inject('Carbon', '\Carbon\Carbon')

@if(in_array('export', $buttonType))
	<a class="btn btn-success pull-right b-exportReport" href="#" data-model="{{ $model }}" data-params="{{ $params }}">@lang("admin/common.Export")</a>
@endif

@if(in_array('fetchCurrencies', $buttonType))
	<a class="btn btn-success pull-right" href="{{ route('admin.currencies.fetch') }}">@lang("admin/common.FetchCurrencies")</a>
@endif

@if(in_array('reGenerateReport', $buttonType))
	<div class="col-md-3 pull-right">
		{!! Form::open(['route' => ['admin.reports.reGenerateReport', $model], 'class' => '', 'method' => "GET"]) !!}
		<div class="col-md-7">
			@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date', 'value' => $Carbon::today(), 'label' => null, 'date' => true])
		</div>
		<div class="col-md-5">
			<button class="btn btn-primary" type="submit">@lang("admin/common.Refresh")</button>
		</div>
		{!! Form::close() !!}
	</div>
@endif

@if(in_array('restartLoop', $buttonType))
	<?php ['type' => $type, 'id' => $id] = json_decode($params, true); ?>
	<a class="btn btn-success pull-right" href="{{ route('admin.rounds.restart', [$type, $id]) }}">@lang("admin/common.RestartLoop")</a>
	<a class="btn btn-primary pull-right margin-r-5" href="{{ route('admin.rounds.restartWithNoBets', [$type, $id]) }}">@lang("admin/common.RestartLoopWithNoBets")</a>
@endif

@if(in_array('refreshDataTable', $buttonType))
	<button class="btn btn-success" onclick="$('.datatables').DataTable().api().draw();">@lang("admin/common.RefreshDataTable")</button>
@endif