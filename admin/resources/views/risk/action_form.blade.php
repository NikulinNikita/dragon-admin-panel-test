<div class="form-elements">
    <input type="hidden" name="risk_event_id" value="{{ $risk_event_id }}">
    <div class="form-group form-element-select ">
        <label for="assigned_status" class="control-label">{{ trans('admin/risk_event_staff_actions.status') }}</label>
        <div>
            <select name="assigned_status" id="assigned_status">
                @foreach (config('selectOptions.risk_event_staff_actions.status') as $status)
                    <option {{ $current_status == $status ? 'selected' : '' }} value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group form-element-textarea ">
        <label for="comment" class="control-label">{{ trans('admin/risk_event_staff_actions.comment') }}</label>
        <textarea rows="10" id="comment" name="comment" class="form-control" style="resize: none"></textarea>
    </div>
</div>