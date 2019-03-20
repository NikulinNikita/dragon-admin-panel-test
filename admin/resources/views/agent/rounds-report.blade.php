<h4>@lang('admin/agents.rounds.title', ['username' => $playerUser->name])</h4>
<p>@lang('admin/agents.rounds.subtitle', ['username' => $agentUser->name])</p>
<hr>

<table class="table table-striped table-hover table-condensed table-responsive b-reports-table text-center">
    @php ($totalBets = $totalPayouts = $totalBetsBank = $totalRewards = 0)
    <thead>
        <tr>
            <th>@lang('admin/agents.rounds.date_time')</th>
            <th>@lang('admin/agents.rounds.game')</th>
            <th>@lang('admin/agents.rounds.table')</th>
            <th>@lang('admin/agents.rounds.bet')</th>
            <th>@lang('admin/agents.rounds.payout')</th>
            <th>@lang('admin/agents.rounds.bets_bank')</th>
            <th>@lang('admin/agents.rounds.rewards')</th>
        </tr>
    </thead>
    <tbody>
    {{--
    @foreach ($rounds as $round)
        @php ($totalBets+= $round['statistics']['betsAmount'])
        @php ($totalPayouts+= $round['statistics']['payoutsAmount'])
        @php ($totalBetsBank+= $round['statistics']['betsBankAmount'])
        @php ($totalRewards+= $round['statistics']['rewardAmount'])
        <tr>
            <td>{{ \Carbon\Carbon::parse($round['round']['created_at'])->toDayDateTimeString() }}</td>
            <td>{{ $round['round']['game'] }}</td>
            <td>{{ $round['round']['table'] }}</td>
            @php ($uri = isset($round['round']['baccarat_shoe_id'])
                ? BaseModel::generateUrl('BaccaratBet', ['baccarat_round_id' => $round['round']['id'], 'user_till_id' => $playerUser->MoneyTill->id])
                : BaseModel::generateUrl('RouletteBet', ['roulette_round_id' => $round['round']['id'], 'user_till_id' => $playerUser->MoneyTill->id])
            )
            <td><a href="{{ $uri }}">{{ BaseModel::formatCurrency(1, $round['statistics']['betsAmount']) }}</a></td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['payoutsAmount']) }}</td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['betsBankAmount']) }}</td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['rewardAmount']) }}</td>
        </tr>
    @endforeach
    --}}
    @foreach ($rounds as $round)
        @php ($totalBets+= $round['statistics']['bets_total_default_amount'])
        @php ($totalPayouts+= $round['statistics']['bets_total_default_outcome'])
        @php ($totalBetsBank+= $round['statistics']['bets_bank_total_default_amount'])
        @php ($totalRewards+= $round['statistics']['rewardAmount'])
        <tr>
            <td>{{ \Carbon\Carbon::parse($round['round']['created_at'])->toDayDateTimeString() }}</td>
            <td>{{ $round['round']['game'] }}</td>
            <td>{{ $round['round']['table'] }}</td>
            @php ($uri = isset($round['round']['baccarat_shoe_id'])
                ? BaseModel::generateUrl('BaccaratBet', ['baccarat_round_id' => $round['round']['id'], 'user_till_id' => $playerUser->MoneyTill->id])
                : BaseModel::generateUrl('RouletteBet', ['roulette_round_id' => $round['round']['id'], 'user_till_id' => $playerUser->MoneyTill->id])
            )
            <td><a href="{{ $uri }}">{{ BaseModel::formatCurrency(1, $round['statistics']['bets_total_default_amount']) }}</a></td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['bets_total_default_outcome']) }}</td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['bets_bank_total_default_amount']) }}</td>
            <td>{{ BaseModel::formatCurrency(1, $round['statistics']['rewardAmount']) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">
                Итого:
            </th>
            <td><b>{{ BaseModel::formatCurrency(1, $totalBets) }}</b></td>
            <td><b>{{ BaseModel::formatCurrency(1, $totalPayouts) }}</b></td>
            <td><b>{{ BaseModel::formatCurrency(1, $totalBetsBank) }}</b></td>
            <td><b>{{ BaseModel::formatCurrency(1, $totalRewards) }}</b></td>
        </tr>
    </tfoot>
</table>