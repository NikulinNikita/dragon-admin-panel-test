<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
        <tr>
            <th>@lang('admin/agent_rewards.agent_level')</th>
            <td>@lang('admin/agent_rewards.amount_charges')</td>
        </tr>
    @foreach ($levelStatistics as $level => $statistic)
        <tr>
            <td>{{ $level }}</td>
            <td>{{ BaseModel::formatCurrency(1, $statistic) }}</td>
        </tr>
    @endforeach
</table>