<table class="table">
    <tbody>
        <thead>
            <tr>
                <th>Bet ID</th>
                <th>Game</th>
                <th>Amount</th>
                <th>Outcome</th>
                <th>Profit</th>
                <th>Default Amount</th>
                <th>Default Outcome</th>
                <th>Default Profit</th>
                <th>Date</th>
            </tr>
        </thead>
        @foreach ($collection as $item)
            <tr>
                <td>{{$item->id}}</td>
                <td>{{$item->game}}</td>
                <td>{{$item->amount}}</td>                
                <td>{{$item->outcome}}</td>
                <td>{{$item->profit}}</td>
                <td>{{$item->default_amount}}</td>
                <td>{{$item->default_outcome}}</td>
                <td>{{$item->default_profit}}</td>
                <td>{{$item->created_at}}</td>
            </tr>
        @endforeach
    </tbody>
</table>