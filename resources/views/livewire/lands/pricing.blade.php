<div>
    <x-form.search-box wire:model="search" />

    @if ($pricings->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>مبلغ قیمت گذاری psc</th>
                <th>مبلغ قیمت گذاری ریال</th>
                <th>تاریخ قیمت گذاری</th>
                <th>ساعت قیمت گذاری</th>
            </x-slot:headers>
            @foreach ($pricings as $pricing)
                <tr wire:key="{{ $pricing->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pricing->feature->properties->id }}</td>
                    <td>{{ $pricing->price_psc }}</td>
                    <td>{{ $pricing->price_irr }}</td>
                    <td>{{ jdate($pricing->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($pricing->created_at)->format('H:m:s') }}</td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="danger" :message="'ملکی یافت نشد'" />
    @endif
</div>
