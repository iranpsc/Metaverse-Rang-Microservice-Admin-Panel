<div>
    <x-form.search-box wire:model="search" />

    @if (count($trades) > 0)
        <x-table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>خریدار</th>
                <th>تاریخ خرید</th>
                <th>ساعت خرید</th>
            </x-slot:headers>
            @foreach ($trades as $trade)
                <tr wire:key="{{ $trade->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $trade->feature->properties->id }}</td>
                    <td>{{ $trade->buyer->name }}</td>
                    <td>{{ jdate($trade->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($trade->created_at)->format('H:m:s') }}</td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="danger" :message="'ملکی یافت نشد'" />
    @endif
</div>
