<div>
    {{-- Stop trying to control. --}}

    @if ($variables->count() > 0)
        @php
            $variables = $variables->reject(function ($variable) {
                return is_null($variable->priceChangeLogs);
            });
        @endphp
        <x-tables.table id="variables-price-change-table">
            <x-slot name="headers">
                <th>دارایی</th>
                <th>تاریخ تغییر</th>
                <th>ساعت تغییر</th>
                <th>تغییر دهنده</th>
                <th>وضعیت گذشته</th>
                <th>وضعیت حال</th>
                <th>توضیحات</th>
                <tbody>
                    @foreach ($variables as $variable)
                        @foreach ($variable->priceChangeLogs as $priceChangeLog)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>ارز {{ \App\Helpers\getAssetColor($variable->asset) }}</td>
                                <td>{{ \Morilog\Jalali\Jalalian::forge($priceChangeLog->created_at)->format('Y/m/d') }}
                                </td>
                                <td>{{ \Morilog\Jalali\Jalalian::forge($priceChangeLog->created_at)->format('H:m:s') }}
                                </td>
                                <td>{{ $priceChangeLog->changer_name }}</td>
                                <td>{{ $priceChangeLog->previous_price }}</td>
                                <td>{{ $priceChangeLog->current_price }}</td>
                                <td>{{ $priceChangeLog->note }}</td>
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
            $('#variables-price-change-table').DataTable();
        </script>
    @endpush

</div>
