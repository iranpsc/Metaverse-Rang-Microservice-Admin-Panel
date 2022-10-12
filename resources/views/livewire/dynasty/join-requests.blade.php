<div>
    @if ($joinRequests->count() > 0)
        <table class="table table-striped">
            <thead>
                <th>ردیف</th>
                <th>از</th>
                <th>به</th>
                <th>نسبت خانوادگی</th>
                <th>گواهی</th>
                <th>وضعیت</th>
                <th>مدیریت</th>
            </thead>
        </table>

        @foreach ($joinRequests as $request)
            <tr>
                <td>{{ $iteration->loop }}</td>
                <td>{{ $request->fromUser->name }}</td>
                <td>{{ $request->toUser->name }}</td>
                <td>{{ \App\Helpers\getRelationTitle($request->relation) }}</td>
                <td></td>
                <td>{{ $request->status }}</td>
            </tr>
        @endforeach
    @else
        <x-alert :type="'danger'" :message="'درخواستی ثبت نشده است'"></x-alert>
    @endif

</div>
