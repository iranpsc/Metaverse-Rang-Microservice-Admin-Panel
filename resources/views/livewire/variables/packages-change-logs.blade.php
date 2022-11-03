<div>
    {{-- Stop trying to control. --}}

    @if ($priceChagneLogs->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>کد پکیج</th>
                <th>تاریخ تغییر</th>
                <th>ساعت تغییر</th>
                <th>تغییر دهنده</th>
                <th>وضعیت گذشته</th>
                <th>وضعیت حال</th>
                <th>توضیحات</th>
                <tbody>
                    @foreach ($priceChagneLogs as $priceChagneLog)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $priceChagneLog->option->code }}</td>
                            <td>{{ \Morilog\Jalali\Jalalian::forge($priceChagneLog->created_at)->format('Y/m/d') }}
                            </td>
                            <td>{{ \Morilog\Jalali\Jalalian::forge($priceChagneLog->created_at)->format('H:m:s') }}
                            </td>
                            <td>{{ $priceChagneLog->changer_name }}</td>
                            <td>{{ $priceChagneLog->previous_price }}</td>
                            <td>{{ $priceChagneLog->current_price }}</td>
                            <td>{{ $priceChagneLog->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-slot>
        </x-tables.table>
    @else
        <x-alerts.danger>لاگ تغییری ثبت نشده است</x-alerts.danger>
    @endif
</div>
