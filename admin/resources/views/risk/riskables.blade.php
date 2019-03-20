<div class="panel panel-default">
    <table class="table-default table-hover table table-striped">
        <thead>
            <tr>
                <th>{{ trans('admin/risk_events.type') }}</th>
                <th>{{ trans('admin/risk_events.riskable_id') }}</th>
                <th></th>
            </tr>
        </thead>
        @foreach ($riskables as $riskable)
            <tr>
                <td>{{ $riskable->riskable_type }}</td>
                <td>{{ $riskable->riskable_id }}</td>
                <td><a href="{{ route('admin.model.edit', ['adminModel' => ($riskable->riskable_type != 'staff' ? $riskable->riskable_type . 's' : $riskable->riskable_type), 'adminModelId' => $riskable->riskable_id]) }}">Посмотреть</a></td>
            </tr>
        @endforeach
    </table>
</div>