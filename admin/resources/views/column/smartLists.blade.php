@if($showTags ?? null)
	@foreach ($values as $value)
		<span class="label label-info">{{ $value }}</span>
	@endforeach
	{!! BaseModel::replaceTags($append) !!}
@else
	@foreach ($values as $value)
		<span class="label label-info">{{ $value }}</span>
	@endforeach
	{{ $append }}
@endif

