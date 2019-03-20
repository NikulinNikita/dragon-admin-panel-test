@if($showTags ?? null)
	<div {!! $attributes !!}>{!! BaseModel::replaceTags($value) !!} {!! BaseModel::replaceTags($append) !!}
		@if($small)
			<small class="clearfix">{!! BaseModel::replaceTags($small) !!}</small>
		@endif
	</div>
@else
	<div {!! $attributes !!}>{{ $value }} {{ $append }}
		@if($small)
			<small class="clearfix">{{ $small }}</small>
		@endif
	</div>
@endif
