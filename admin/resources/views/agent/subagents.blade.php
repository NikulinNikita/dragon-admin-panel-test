<ol class="agent-list">
    @foreach ($subAgents as $subAgent)
        {{--
        <li class="agent">
            <div class="table-agent-info">
                <table id="agent-info-{{ $subAgent->id }}" class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
                    <caption>@lang('admin/reports_agents.player_info', ['player' => $subAgent->user->name])</caption>
                    <tr>
                        <th>
                            @lang('admin/reports_agents.gaming_bets_bank')
                        </th>
                        <td>{{ $statistics[$subAgent->id]['ownBetsBank'] }}</td>
                    </tr>
                    <tr>
                        <th>
                            @lang('admin/reports_agents.partner_bets_bank')
                        </th>
                        <td>{{ $statistics[$subAgent->id]['betsBank'] }}</td>
                    </tr>
                    {{--
                    <tr>
                        <th>@lang('admin/reports_agents.level_reward_percent')</th>
                        <td>{{ $statistics[$subAgent->id]['levelRewardPercent'] }}</td>
                    </tr>
                    
                    <tr>
                        <th>@lang('admin/reports_agents.reward_amount')</th>
                        <td>{{ $statistics[$subAgent->id]['rewardAmount'] }}</td>
                    </tr>
                </table>
            </div>
            @if ($subAgent->children->count() > 0)
            <button data-parent-id="{{ $subAgent->id }}" class="expand closed">Expand</button>
            @endif
            <div class="agent-title" id="{{ $subAgent->id }}">@lang('admin/reports_agents.player') {{ $subAgent->user->name }}</div>
        </li>
        --}}
        <li class="agent">
            <div class="table-agent-info">
                <table id="agent-info-{{$subAgent->id}}" class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
                    <caption>@lang('admin/reports_agents.player_info', ['player' => $subAgent->user->name])</caption>
                    <tr>
                        <th></th>
                        <th>@lang('admin/reports_agents.agent')</th>
                        <th>@lang('admin/reports_agents.subagent')</th>
                    </tr>
                    <tr>
                        <td>@lang('admin/reports_agents.gaming_bets_bank')</td>
                        <td colspan="2">{{ $statistics[$subAgent->id]['ownBetsBank'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('admin/reports_agents.count')</td>
                        <td>{{ $statistics[$subAgent->id]['agentsCount'] }}</td>
                        <td>{{ $statistics[$subAgent->id]['subAgentsCount'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('admin/reports_agents.partner_bets_bank')</td>
                        <td>{{ $statistics[$subAgent->id]['betsBank'] }}</td>
                        <td>{{ $statistics[$subAgent->id]['subAgentsBetsBank'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('admin/reports_agents.level_reward_percent')</td>
                        <td>{{ $statistics[$subAgent->id]['firstLevelRewardPercent'] }}</td>
                        <td>{{ $statistics[$subAgent->id]['subAgentRewardPercent'] }}</td>
                    </tr>
                    <tr>
                        <td>@lang('admin/reports_agents.reward_amount')</td>
                        <td>{{ $statistics[$subAgent->id]['rewardAmount'] }}</td>
                        <td>{{ $statistics[$subAgent->id]['subAgentsRewardAmount'] }}</td>
                    </tr>
                </table>
            </div>
            <button
                    data-deep-level="1"
                    data-parent-id="{{ $subAgent->id }}"
                    class="expand closed">
            </button>
            <div
                    id="{{ $subAgent->id }}"
                    class="agent-title">
                @lang('admin/reports_agents.player') {{ $subAgent->user->name }}
            </div>
        </li>
    @endforeach
</ol>