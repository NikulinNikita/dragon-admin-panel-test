@php
	if(!function_exists('formatCurrency')) {
		function formatCurrency($currencyId, $amount, $exportToExcel = null)
		{
			if(!$exportToExcel)
				{$result = BaseModel::formatCurrency($currencyId, $amount);}
			else
				{$result = $amount;}

			return $result;
		}
	}
@endphp

@if(!isset($exportToExcel))
	<span>
		<span style="font-size: 18px;">&#9632</span> - Номинальная сумма;
		<span style="color:#4c4c8a;font-size: 18px;">&#9632</span> - Дефолтная (сконвертированная на момент создания) сумма;
		<span style="color:#c21313;font-size: 18px;">&#9632</span> - Сконвертированная (по текущему курсу) сумма
	</span>
@endif

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table">
	<tr>
		@foreach($preRows ?? [] as $columnName => $rowName)
			<th>{{ $columnName }}</th>
		@endforeach
		<th>@lang("admin/common.Currency")</th>
		@foreach($rows as $columnName => $rowName)
			<th>{{ $columnName }}</th>
		@endforeach
		@if(env('APP_ENV', 'prod') === 'local' && isset($hiddenColumns))
			<th>{{ $columnName }} (x)</th>
		@endif
	</tr>
	@foreach(array_first($rows) as $k => $rowName)
		@foreach($currencies as $currencyId => $currencyCode)
			@foreach($attrTypes as $attrType)
				@php
					$nominal = $attrType === 'nominal';
					$curId = $nominal ? $currencyId : 1;
					$color = $nominal ? 'black' : ($attrType === 'default' ? '#4c4c8a' : '#c21313');
				@endphp

				<tr @if($nominal) class="accordion-toggle" data-toggle="collapse" data-target=".collapse_{{ $tableName }}_{{ $k }}_{{ $currencyCode }}"
				    @else class="accordion-body collapse collapse_{{ $tableName }}_{{ $k }}_{{ $currencyCode }}" @endif>
					@foreach($preRows ?? [] as $columnName => $rowNames)
						<td>
							<b>{{ $currencyId === 1 && $nominal ? $rowNames[$k] : '' }}</b>
						</td>
					@endforeach
					<td>{!! $nominal ? ('<b>(+)</b> ' . $currencyCode) :
					("<b style='visibility:hidden'>(+)</b> " . $currencyCode . " <b style='color:{$color}'>({$attrType})</b>") !!}</td>
					@foreach($rows as $columnName => $rowNames)
						<td>
							<b style="color:{{ $color }}">{{ formatCurrency($curId, $obj->{$rowNames[$k]}->{$attrType}->{$currencyCode}, $exportToExcel) }}</b>
						</td>
					@endforeach

					@if(env('APP_ENV', 'prod') === 'local' && isset($hiddenColumns))
						<td>
							<b style="color:{{ $color }}">{{ formatCurrency($curId, $obj->{$hiddenColumns[$k]}->{$attrType}->{$currencyCode}, $exportToExcel) }}</b>
						</td>
					@endif
				</tr>
			@endforeach
		@endforeach
	@endforeach

	@foreach(array_slice($attrTypes, 1) as $attrType)
		@php
			$color = $nominal ? 'black' : ($attrType === 'default' ? '#4c4c8a' : '#c21313');
		@endphp

		<tr @if($attrType === 'default') class="danger accordion-toggle" data-toggle="collapse" data-target=".collapse_{{ $tableName }}_total"
		    @else class="accordion-body collapse collapse_{{ $tableName }}_total" @endif>
			<td><b>{!! $attrType === 'default' ? ('<b>(+)</b> ' . trans("admin/common.Total") . ':') :
			("<b style='visibility:hidden'>(+)</b> " . trans("admin/common.Total") . " <b style='color:{$color}'>({$attrType})</b>:") !!}</b></td>
			@if(isset($preRows) && count($preRows))
				<td></td>
			@endif
			@foreach($rows as $columnName => $rowNames)
				@php
					$counter = ($counter ?? 0) + 1;
					foreach ($rowNames as $k => $rowName) {
						$amount[$attrType][$counter] = ($amount[$attrType][$counter] ?? 0) + $obj->{$rowName}->{$attrType}->{'total'};
					}
				@endphp

				<td><b style="color:{{ $color }}">{{ formatCurrency($curId, $amount[$attrType][$counter], $exportToExcel) }}</b></td>
			@endforeach

			@if(env('APP_ENV', 'prod') === 'local' && isset($hiddenColumns))
				@php
					foreach ($hiddenColumns as $k => $hiddenColumn) {
						$amount[$attrType]['hidden'] = ($amount[$attrType]['hidden'] ?? 0) + $obj->{$hiddenColumn}->{$attrType}->{'total'};
					}
				@endphp

				<td><b style="color:{{ $color }}">{{ formatCurrency($curId, $amount[$attrType]['hidden'], $exportToExcel) }}</b></td>
			@endif
		</tr>
	@endforeach
</table>