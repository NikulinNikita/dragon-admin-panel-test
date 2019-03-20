@if($showTags ?? null)
	@if($isEditable)
		<a href="{{ $link }}" {!! app('html')->attributes($linkAttributes) !!}>
			{!! $value !!}
		</a>
	@else
		{!! BaseModel::replaceTags($value) !!}
	@endif
	{!! BaseModel::replaceTags($append) !!}
	@if($small)
		<small class="clearfix">{!! BaseModel::replaceTags($small) !!}</small>
	@endif
@else
	@if($isEditable)
		<a href="{{ $link }}" {!! app('html')->attributes($linkAttributes) !!}>
			{{ $value }}
		</a>
	@else
		{{ $value }}
	@endif
	{{ $append }}
	@if($small)
		<small class="clearfix">{{ $small }}</small>
	@endif
@endif