@if($showTags ?? null)
	<div {!! $attributes !!}>
		@if (!empty($value))
			{!! HTML::mailto($value, $value) !!}
		@endif
		{!! BaseModel::replaceTags($append) !!}
	</div>
@else
	<div {!! $attributes !!}>
		@if (!empty($value))
			{{ HTML::mailto($value, $value) }}
		@endif
		{{ $append }}
	</div>
@endif


