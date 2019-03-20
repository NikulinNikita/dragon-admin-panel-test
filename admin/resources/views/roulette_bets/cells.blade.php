@if ($cells)
    <div class="form-group form-element-select ">
        <label for="staff_session_id" class="control-label">@lang("admin/roulette_bets.{$title}")</label>
        <div>
            @foreach ($cells as $cell)
                <span style="display: inline-block; background: {{$cell->color}}; color: #fff;padding: 5px; margin: 2px;">{{$cell->value}}</span>
            @endforeach
        </div>
    </div>
@endif