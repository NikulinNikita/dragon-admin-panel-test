<?php
$params  = collect(Request::all());
$regions = \App\Models\Region::join('region_translations', 'regions.id', '=', 'region_translations.region_id')->pluck('title', 'title');
?>

<div class="container-fluid Search-form">
	{!! Form::open(['route' => ['admin.search'], 'class' => '']) !!}
	<div class="panel panel-default">
		<div class="panel-heading">@lang('admin/users.search.MainInformation')</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
						<label for="select_date_period">@lang('admin/users.search.RegistrationDate'):</label>
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
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'name', 'var' => $params, 'label' => trans("admin/users.name")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'id', 'var' => $params, 'label' => 'ID'])
				</div>
				<div class="col-md-3">
					{!! AdminFormElement::select('region', 'Region', $regions->all()) !!}
				</div>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">@lang('admin/users.search.PersonalInformation')</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'first_name', 'var' => $params, 'label' => trans("admin/users.first_name")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'last_name', 'var' => $params, 'label' => trans("admin/users.last_name")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'middle_name', 'var' => $params, 'label' => trans("admin/users.middle_name")])
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'email', 'var' => $params, 'label' => trans("admin/users.email")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'phone', 'var' => $params, 'label' => trans("admin/users.phone")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'mobile', 'var' => $params, 'label' => trans("admin/users.mobile")])
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'birthday', 'var' => $params,
					'label' => trans("admin/users.birthday"), 'date' => true])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'birth_city', 'var' => $params, 'label' => trans("admin/users.birth_city")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => "select", 'item' => 'gender', 'label' => trans("admin/users.gender"),
						'list_fixed' => array_combine(config('selectOptions.common.gender'), config('selectOptions.common.gender')),
						'selected' => $params, 'placeholder' => "Both", 'dp' => "class=\"form-group\""])
				</div>
			</div>
			{{--<div class="row">--}}
			{{--<div class="col-md-3">--}}
			{{--@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'notes', 'var' => $params, 'label' => "<span class='badge'>?</span>Касса"])--}}
			{{--</div>--}}
			{{--<div class="col-md-3">--}}
			{{--@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'email5', 'var' => $params, 'label' => "<span class='badge'>?</span>IP Адрес"])--}}
			{{--</div>--}}
			{{--</div>--}}
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">@lang('admin/users.search.FinancialInformation')</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'balance_from', 'var' => $params,
					'label' => trans("admin/users.search.BalanceMoreOrEqualTo")])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'balance_to', 'var' => $params,
					'label' => trans("admin/users.search.BalanceLessThen")])
				</div>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">@lang('admin/users.search.UserStatus')</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => "select", 'item' => 'status', 'label' => trans("admin/users.status"),
						'list_fixed' => array_combine(config('selectOptions.common.status'), config('selectOptions.common.status')),
						'selected' => $params, 'placeholder' => "Both", 'dp' => "class=\"form-group\""])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => "select", 'item' => 'document_verification', 'label' => trans("admin/users.document_verification"),
						'list_fixed' => array_combine(config('selectOptions.users.verification'), config('selectOptions.users.verification')),
						'selected' => $params, 'placeholder' => "Both", 'dp' => "class=\"form-group\""])
				</div>
				<div class="col-md-3">
					@include('admin::defaults._inputBlocks', ['type' => "select", 'item' => 'birth_date_verification', 'label' => trans("admin/users.birth_date_verification"),
						'list_fixed' => array_combine(config('selectOptions.users.verification'), config('selectOptions.users.verification')),
						'selected' => $params, 'placeholder' => "Both", 'dp' => "class=\"form-group\""])
				</div>
			</div>
		</div>
	</div>
	<button type="submit" class="btn btn-primary global-filter-button">@lang('admin/common.Filter')</button>
	<a class="btn btn-danger" href="{{ url('admin_panel/users') }}">@lang('admin/common.Cancel')</a>
	{!! Form::close() !!}
</div>
