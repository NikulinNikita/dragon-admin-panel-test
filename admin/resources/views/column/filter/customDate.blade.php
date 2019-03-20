<div class="form-group" style="margin-bottom: 5px; width:{{ $width }}px">
	<select name="select_date_period" id="select_date_period" class="form-control select_date_period" style="width: inherit;">
		<option value="custom">Custom</option>
		<option value="all">All</option>
		<option value="today">Today</option>
		<option value="yesterday">Yesterday</option>
		<option value="last_3_days">Last 3 days</option>
		<option value="last_week">Last week</option>
		<option value="last_month">Last month</option>
	</select>
</div>
<div class="input-date form-group input-group" style="width:{{ $width }}px">
	<input
			data-date-format="{{ $pickerFormat }}"
			data-date-useseconds="{{ $seconds ? 'true' : 'false' }}"
			class="form-control column-filter"
			type="text"
			placeholder="{{ $placeholder }}"
			{!! $attributes !!} >

	<span class="input-group-addon">
		<span class="fa fa-calendar"></span>
	</span>
</div>
