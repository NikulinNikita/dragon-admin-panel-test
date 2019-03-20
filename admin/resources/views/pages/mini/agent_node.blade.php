<ol class="agents-list">
    <li class="agent">
        @if (isset($statistics))
            <div class="table-agent-info">
            <table id="agent-info-{{$agent->id}}" class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
                <caption>@lang('admin/reports_agents.player_info', ['player' => $agent->user->name])</caption>
                <tr>
                    <th></th>
                    <th>@lang('admin/reports_agents.agent')</th>
                    <th>@lang('admin/reports_agents.subagent')</th>
                </tr>
                <tr>
                    <td>@lang('admin/reports_agents.gaming_bets_bank')</td>
                    <td colspan="2">{{ $agentStatistics['ownBetsBank'] }}</td>
                </tr>
                <tr>
                    <td>@lang('admin/reports_agents.count')</td>
                    <td>{{ $agentStatistics['agentsCount'] }}</td>
                    <td>{{ $agentStatistics['subAgentsCount'] }}</td>
                </tr>
                <tr>
                    <td>@lang('admin/reports_agents.partner_bets_bank')</td>
                    <td>{{ $agentStatistics['betsBank'] }}</td>
                    <td>{{ $agentStatistics['subAgentsBetsBank'] }}</td>
                </tr>
                <tr>
                    <td>@lang('admin/reports_agents.level_reward_percent')</td>
                    <td>{{ $agentStatistics['firstLevelRewardPercent'] }}</td>
                    <td>{{ $agentStatistics['subAgentRewardPercent'] }}</td>
                </tr>
                <tr>
                    <td>@lang('admin/reports_agents.reward_amount')</td>
                    <td>{{ $agentStatistics['rewardAmount'] }}</td>
                    <td>{{ $agentStatistics['subAgentsRewardAmount'] }}</td>
                </tr>
            </table>
        </div>
        @endif
        @if (isset($expand))
            <button
                data-deep-level="{{ $node->depth + 1 }}"
                data-parent-id="{{ $node->id }}"
                class="expand closed">
            </button>
        @endif
        <div
            @if (!isset($expand)) style="background-color: #f0f0f0; cursor: default" @endif
            id="{{ $node->id }}"
            class="agent-title">
            @lang('admin/reports_agents.player') {{ $node->user->name }}
        </div>
        @if (isset($children) && isset($index))
            @if (isset($children[$index]))
                @include('admin::pages.mini.agent_node', ['node' => $children[$index], 'children' => $children, 'index' => $index + 1])
            @else
                <?php unset($index) ?>
                @include('admin::pages.mini.agent_node', ['node' => $agent, 'expand' => true, 'statistics' => $agentStatistics])
            @endif
        @endif
    </li>
</ol>