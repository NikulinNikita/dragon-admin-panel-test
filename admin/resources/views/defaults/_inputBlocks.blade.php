<?php
	$formGroup = $type != 'checkbox' && $type != 'radio' ? "form-group" : "";
	$defaultClassForDiv = "class=\"{$formGroup} \"";
	$p = isset($p) && $p ? $p . ' ' . "id=\"{$item}\" class=\"form-control\"" : "id=\"{$item}\" class=\"form-control\"";
?>
@if(!isset($dp) || (isset($dp) && $dp != 'no-div'))
	<div {!! isset($dp) && $dp ? $dp . ' ' . $defaultClassForDiv : $defaultClassForDiv !!}>
@endif
	@if(isset($label))
		<label for="{{ $item }}" {!! isset($lp) && $lp !!}>{!! $label !!}:</label>
	@endif
	@if(isset($sdp) && $sdp)
		<div {!! $sdp !!}>
	@endif

		@if($type === 'input')
			<input type="text" name="{{ $item }}" value="{{ old($item) ? old($item) : (isset($var) && isset($var->{$item}) ? $var->{$item} :
				(isset($var) ? $var->get($item) : @$value))}}" {!! $p !!}>

		@elseif($type === 'select' || $type === 'multiSelect')
            <?php
	            $listedItems = isset($list_fixed) ? $list_fixed : (count($list) ? $list->lists($type === 'multiSelect' ? 'name' : 'title', 'id') : []);
	            if($type === 'multiSelect') {
	                $selectedItemId = old($item) ? old($item) : (isset($selected_fixed) ? $selected_fixed : (isset($selected) ? $selected->lists('id')->all() : null));
	                $selectedItemId = $selectedItemId ? array_flip($selectedItemId) : null;
	            } else
	                $selectedItemId = old($item) ? old($item) : (isset($selected_fixed) ? $selected_fixed :
		                (isset($selected) && isset($selected->{$item}) ? $selected->{$item} : (isset($selected) ? $selected->get($item) : null)));
            ?>

			<select name="{{ $item }}" {!! $p !!}>
				@if(isset($placeholder)) <option value="">{{ $placeholder }}</option> @endif
				@foreach($listedItems as $id => $title)
					<option value="{{ $id }}" @if($type === 'multiSelect' ? array_get($selectedItemId, $id) !== null : $id == $selectedItemId) selected @endif>{{ $title }}</option>
				@endforeach
			</select>

		@elseif($type === 'dateTime')
			<div class="input-date input-group">
				<input type="text" name="{{ $item }}" value="{{ old($item) ? old($item) : (isset($var) && isset($var->{$item}) ? $var->{$item} :
					(isset($var) ? $var->get($item) : @$value))}}" data-date-format="YYYY-MM-DD" {!! $p !!}
			        data-date-pickdate="{{ isset($date) ? $date : 0 }}" data-date-picktime="{{ isset($time) ? $time : 0 }}" data-date-useseconds="{{ isset($seconds) ? $seconds : 0 }}">
				<span class="input-group-addon"><span class="fa fa-calendar"></span></span>
			</div>
		@endif

	@if(@$sdp)
		</div>
	@endif
@if(@$dp != 'no-div')
	</div>
@endif