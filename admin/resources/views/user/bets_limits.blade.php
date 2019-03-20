@foreach ($games as $game)
    <div class="col-md-6">
        <h3>{{ ucwords($game->title) }}</h3>
        <div class="form-elements">
            <div data-type="range" class="column-filter">
                <label for="password" class="control-label">{{ trans("admin/users.bets_limit.min") }}</label>
                <input type="text" class="form-control column-filter" name="bets_limit[{{$game->slug}}][bet_limit_min]" value="{{isset($currentBetsLimit->{$game->slug}->bet_limit_min) ? $currentBetsLimit->{$game->slug}->bet_limit_min : ''}}">
                
                <div style="margin-top: 5px;"></div>
                
                <label for="password" class="control-label">{{ trans("admin/users.bets_limit.max") }}</label>
                <input type="text" class="form-control column-filter" name="bets_limit[{{$game->slug}}][bet_limit_max]" value="{{isset($currentBetsLimit->{$game->slug}->bet_limit_max) ? $currentBetsLimit->{$game->slug}->bet_limit_max : ''}}">
            </div>
        </div>
    </div>
@endforeach