<div>
    @if ($users->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>شناسه شهروند</th>
                <th>کل زمان حضور</th>
                <th>تعداد مشترکین</th>
                <th>تعداد مشارکت</th>
                <th>کل امتیاز دریافتی</th>
            </x-slot:headers>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->code }}</td>
                    <td>{{ $user->activities->sum('total') }}</td>
                    <td>{{ $user->followers->count() }}</td>
                    <td>--</td>
                    <td>{{ $user->log->score ?? 0 }}</td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>کاربری یافت نشد</x-alerts.danger>
    @endif

</div>

