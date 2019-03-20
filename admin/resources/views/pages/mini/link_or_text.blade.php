<?php
$content = $var;
$condition = $cond ?? $var;
?>

@if($condition && $link)
	<a href="{{ url($link) }}">
		{{ $content }}
	</a>
@else
	{{ $content }}
@endif