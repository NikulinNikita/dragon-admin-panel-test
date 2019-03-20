@if($showTags ?? null)
	@if ( ! empty($value))
		<a href="{{ $value }}" data-toggle="lightbox">
			<img class="thumbnail" src="{{ $value }}" width="{{ $imageWidth }}">
		</a>
	@endif
	{!! BaseModel::replaceTags($append) !!}
@else
	@if ( ! empty($value))
		<a href="{{ $value }}" data-toggle="lightbox">
			<img class="thumbnail" src="{{ $value }}" width="{{ $imageWidth }}">
		</a>
	@endif
	{{ $append }}
@endif

