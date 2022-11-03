<div>
    {{-- Stop trying to control. --}}

    @if ($changeLogs->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>دارایی</th>
                <th>تاریخ تغییر</th>
                <th>ساعت تغییر</th>
                <th>تغییر دهنده</th>
                <th>وضعیت گذشته</th>
                <th>وضعیت حال</th>
                <th>توضیحات</th>
                <tbody>
                    @foreach ($changeLogs as $changeLog)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>ارز {{ \App\Helpers\getAssetColor($changeLog->variable->asset) }}</td>
                            <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('Y/m/d') }}
                            </td>
                            <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('H:m:s') }}
                            </td>
                            <td>{{ $changeLog->changer_name }}</td>
                            <td>{{ $changeLog->previous_price }}</td>
                            <td>{{ $changeLog->current_price }}</td>
                            <td>{{ $changeLog->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-slot>
        </x-tables.table>
    @else
        <x-alerts.danger>لاگ تغییری ثبت نشده است</x-alerts.danger>
    @endif
</div>
