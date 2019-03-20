<h2 class="text-left">{{ $pageHeader }}</h2>

@if(!isset($exportToExcel))
	@include('admin::pages.mini.reports_date_filter', ['route' => ["admin.getReports", $page], 'components' => ['users', 'dateRange', 'noExport']])
@endif

@if (isset($agentStatistics))
	<div class="col-md-12">
		@if ($agentStatistics)
			<div class="dataTables_wrapper">
				<div class="col-md-5">
					@if (count($agent->ancestors))
						@include('admin::pages.mini.agent_node', ['node' => $agent->ancestors[0], 'children' => $agent->ancestors, 'index' => 1])
					@else
						@include('admin::pages.mini.agent_node', ['node' => $agent, 'expand' => true, 'statistics' => $agentStatistics])
					@endif
				</div>
			</div>

			<div class="col-md-7 agent-info"></div>

			<div id="DataTables_Table_0_processing" class="dataTables_processing panel panel-default" style="display: none;">
				<i class="fa fa-5x fa-circle-o-notch fa-spin"></i>
			</div>
		@else
			<p>@lang('admin/agent_rewards.user_is_not_agent')</p>
		@endif
	</div>
@else
	<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
		<tr>
			<th>@lang('admin/agent_rewards.agent_level')</th>
			<td>@lang('admin/agent_rewards.amount_charges')</td>
		</tr>
		@foreach ($levelStatistics as $level => $amount)
			<tr>
				<td>{{ $level }}</td>
				<td>{{ BaseModel::formatCurrency(1, $amount) }}</td>
			</tr>
		@endforeach
	</table>
@endif