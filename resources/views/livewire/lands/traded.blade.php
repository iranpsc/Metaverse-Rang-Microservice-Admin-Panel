<div>
    <x-form.search-box wire:model="search" />

    @if ($trades->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>خریدار</th>
                <th>فروشنده</th>
                <th>تاریخ مبادله</th>
                <th>ساعت مبادله</th>
                <th>مبلغ مبادله psc</th>
                <th>مبلغ مبادله ریال</th>
                <th>کمیسیون سیستم psc</th>
                <th>کمیسیون سیستم ریال</th>
            </x-slot:headers>
            @foreach ($trades as $trade)
                <tr wire:key="{{ $trade->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $trade->feature->properties->id }}</td>
                    <td>{{ $trade->buyer->name }}</td>
                    <td>{{ $trade->seller->name }}</td>
                    <td>{{ jdate($trade->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($trade->created_at)->format('H:m:s') }}</td>
                    <td>{{ $trade->psc_amount }}</td>
                    <td>{{ $trade->irr_amount }}</td>
                    <td>{{ $trade->commision->psc ?? 0 }}</td>
                    <td>{{ $trade->commision->irr ?? 0 }}</td>
                </tr>
            @endforeach
        </x-table>
        {{ $trades->links() }}
    @else
        <x-alert type="danger" :message="'ملکی یافت نشد'" />
    @endif
</div>
