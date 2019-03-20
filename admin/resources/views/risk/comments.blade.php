<div class="panel panel-default">
    <table class="table-default table-hover table table-striped table table-striped">
        <thead>
            <tr>
                <th class="col-md-1 text-center">{{trans('admin/risk_events.created_at')}}</th>
                <th class="col-md-2 text-center">{{trans('admin/risk_events.riskableStaff')}}</th>
                <th class="col-md-1 text-center">{{trans('admin/risk_events.status')}}</th>
                <th class="col-md-8">{{trans('admin/risk_events.message')}}</th>
            </tr>
        </thead>
        @foreach ($comments as $comment)
            <tr>
                <td class="col-md-1 text-center">{{ $comment->created_at }}</td>
                <td class="col-md-2 text-center">{{ $comment->name }}</td>
                <td class="col-md-1 text-center">{{ $comment->assigned_status }}</td>
                <td class="col-md-8">{{ $comment->message }}</td>
            </tr>
        @endforeach    
    </table>
</div>