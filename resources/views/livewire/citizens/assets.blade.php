<div>
    @if (count($assets) > 0)
        <x-tables.table id="assets-table">
            <x-slot:headers>
                <th>نام کاربر</th>
                <th>دارایی های psc</th>
                <th>دارایی های رنگ آبی</th>
                <td>دارایی های رنگ قرمز</td>
                <td>دارایی های رنگ زرد</td>
                <td>دارایی های ریال</td>
                <td>تعداد املاک</td>
            </x-slot:headers>
            @foreach ($assets as $asset)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $asset->user->name }}</td>
                    <td>{{ number_format($asset->psc) }}</td>
                    <td>{{ number_format($asset->blue) }}</td>
                    <td>{{ number_format($asset->red) }}</td>
                    <td>{{ number_format($asset->yellow) }}</td>
                    <td>{{ number_format($asset->irr) }}</td>
                    <td>{{ count($asset->user->features) ?? 0 }}</td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $assets->links() }}
    @else
        <x-alerts.danger>دارایی یافت نشد</x-alerts.danger>
    @endif
</div>
