<script type="application/json" id="status">
	{!! json_encode($status) !!}
</script>

@if(isset($views) && count($views))
	<script type="application/json" id="viewsAmount">
		{!! json_encode(count($views)) !!}
	</script>
	@foreach($views as $k => $view)
		<?php
			if(is_array($view)) {
				$action = key($view);
				$view = reset($view);
			}
		$viewWithoutId =  stristr($view, '-id_', true) ? stristr($view, '-id_', true) : $view;
		?>
		<script type="application/json" id="viewName-{{ $k }}" data-action = "{{ isset($action) && $action ? $action : '' }}">
			{!! json_encode($view) !!}
		</script>
		<script type="application/json" id="content-{{ $k }}">
			@include($viewWithoutId)
		</script>
	@endforeach
@endif
@if(isset($params) && count($params))
	<script type="application/json" id="paramsAmount">
		{!! json_encode(count($params)) !!}
	</script>
	@foreach($params as $paramSelector => $paramName)
		<script type="application/json" id="{{ $paramSelector }}">
			{!! json_encode($paramName) !!}
		</script>
	@endforeach
@endif