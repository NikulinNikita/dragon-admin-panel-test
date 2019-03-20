@if ($cards)
    <div class="form-group">
        @foreach ($cards as $card)
            <img width="70" src="{{asset('img/cards/' . $card . '.png')}}" />
        @endforeach
    </div>
    <hr />
@endif
