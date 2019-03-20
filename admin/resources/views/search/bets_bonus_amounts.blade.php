<?php
$params = collect(Request::all());
?>

<div class="container-fluid Search-form">
	{!! Form::open(['route' => ['admin.search'], 'class' => '']) !!}
	<div class="panel panel-default">
		<div class="panel-heading">@lang('admin/users.search.MainInformation')</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="select_date_period">@lang('admin/agents.search.Period'):</label>
						<select name="period" id="select_date_period" class="form-control select_date_period global_search">
							<option value="custom">@lang('admin/common.Custom')</option>
							<option value="all">@lang('admin/common.All')</option>
							<option value="today">@lang('admin/common.Today')</option>
							<option value="yesterday">@lang('admin/common.Yesterday')</option>
							<option value="last_3_days">@lang('admin/common.Last3days')</option>
							<option value="last_week">@lang('admin/common.LastWeek')</option>
							<option value="last_month">@lang('admin/common.LastMonth')</option>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_from', 'var' => $params,
					'label' => trans("admin/common.DateFrom"), 'date' => true])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_to', 'var' => $params,
					'label' => trans("admin/common.DateTo"), 'date' => true])
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					{!! \AdminFormElement::sSelect('userId', trans("admin/users.name"), \App\Models\User::class)->setDisplay('name')->nullable() !!}
				</div>
			</div>
		</div>
	</div>

	<button type="submit" class="btn btn-primary global-filter-button">@lang('admin/common.Filter')</button>
	<a class="btn btn-danger" href="{{ url('admin_panel/agents') }}">@lang('admin/common.Cancel')</a>
	{!! Form::close() !!}
</div>
