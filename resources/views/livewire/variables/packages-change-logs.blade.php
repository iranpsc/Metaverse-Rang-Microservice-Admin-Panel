<div>
    {{-- Stop trying to control. --}}

    @if ($options->count() > 0)
        @php
            $options = $options->reject(function ($option) {
                return is_null($option->priceChangeLogs);
            });
        @endphp
        <x-tables.table id="packages-price-change-table">
            <x-slot name="headers">
                <th>کد پکیج</th>
                <th>تاریخ تغییر</th>
                <th>ساعت تغییر</th>
                <th>تغییر دهنده</th>
                <th>وضعیت گذشته</th>
                <th>وضعیت حال</th>
                <th>توضیحات</th>
                <tbody>
                    @foreach ($options as $option)
                        @foreach ($option->priceChangeLogs as $priceChagneLog)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $option->code }}</td>
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
                    @endforeach
                </tbody>
            </x-slot>
        </x-tables.table>
    @else
        <x-alerts.danger>لاگ تغییری ثبت نشده است</x-alerts.danger>
    @endif

    @push('js')
        <script>
            $('#packages-price-change-table').DataTable();
        </script>
    @endpush
</div>
