<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="box box-primary">
			<div class="box-body">
				<div class="pull-right">
					@if(!in_array('exportOnly', $components))
						{!! Form::open(['route' => $route, 'class' => '', 'method' => 'GET']) !!}
						<div class="col-md-8">
							<div class="row">
								@if(in_array('dateRange', $components))
									<div class="col-md-4">
										<div class="form-group">
											<label for="select_date_period">@lang('admin/common.SelectDate'):</label>
											<select id="select_date_period" class="form-control select_date_period global_search">
												<option value="custom">@lang('admin/common.Custom')</option>
												<option value="today">@lang('admin/common.Today')</option>
												<option value="yesterday">@lang('admin/common.Yesterday')</option>
												<option value="last_3_days">@lang('admin/common.Last3days')</option>
												<option value="last_week">@lang('admin/common.LastWeek')</option>
												<option value="last_month">@lang('admin/common.LastMonth')</option>
											</select>
										</div>
									</div>
									<div class="col-md-4">
										@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_from', 'value' => $dateFrom,
										'label' => trans("admin/common.DateFrom"), 'date' => true])
									</div>
									<div class="col-md-4">
										@include('admin::defaults._inputBlocks', ['type' => 'dateTime', 'item' => 'date_to', 'value' => $dateTo,
										'label' => trans("admin/common.DateTo"), 'date' => true])
									</div>
								@endif

								@if(in_array('users', $components))
									<div class="col-md-3">
										<div class="form-group">
											{!! \AdminFormElement::sSelect('user_id', trans('admin/common.User'), \App\Models\User::class)->setDisplay('name') !!}
										</div>
									</div>
								@endif

								@if(in_array('currencySelect', $components))
									<div class="col-md-2">
										@include('admin::defaults._inputBlocks', ['type' => 'select', 'item' => 'currency_id', 'label' => trans("admin/common.Currency"),
										'list_fixed' => array_pluck(session()->get('admin.currencies'), 'code', 'id'), 'selected_fixed' => $currencyId,
										'placeholder' => trans("admin/common.All")])
									</div>
								@endif

								@if(in_array('valuesRange', $components))
									<div class="col-md-2">
										@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'value_from', 'value' => $valueFrom,
										'label' => trans("admin/common.ValueFrom")])
									</div>
									<div class="col-md-2">
										@include('admin::defaults._inputBlocks', ['type' => 'input', 'item' => 'value_to', 'value' => $valueTo,
										'label' => trans("admin/common.ValueTo")])
									</div>
								@endif
							</div>
						</div>
						<div class="col-md-2" style="margin-top: 28px">
							<button type="submit" class="btn btn-primary global-filter-button fa fa-filter"></button>
							<a class="btn btn-danger fa fa-times" href="{{ url("/admin_panel/reports/{$page}")}}"></a>
						</div>
						{!! Form::close() !!}
					@endif
					@if(!in_array('noExport', $components))
						<div class="col-md-2 text-right">
							<a class="btn btn-success b-exportStaticReport" style="margin-top: 24px"
							   href="{{ route('admin.getExportStaticReport', [$paramsArr['page'], "date_from" => request()->get('date_from'),
							   "date_to" => request()->get('date_to')]) }}">@lang('admin/common.Export')</a>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>