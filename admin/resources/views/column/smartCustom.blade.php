@if($showTags ?? null)
	<div {!! $attributes !!}>{!! BaseModel::replaceTags($value) !!} {!! BaseModel::replaceTags($append) !!}</div>
@else
	<div {!! $attributes !!}>{{ $value }} {{ $append }}</div>
@endif