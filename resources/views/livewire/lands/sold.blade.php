<div>
    <x-forms.search-box wire:model="search"></x-forms.search-box>
    @if (count($trades) > 0)
        <table class="table table-bordered table-hover table-striped" id="data-table">
            <thead>
                <tr>
                    <th><i class="icon-energy"></i></th>
                    <th>کد زمین</th>
                    <th>خریدار</th>
                    <th>تاریخ خرید</th>
                    <th>ساعت خرید</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trades as $trade)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $trade->feature->properties->id }}</td>
                        <td>{{ $trade->buyer->name }}</td>
                        <td>{{ jdate($trade->created_at)->format('Y/m/d') }}</td>
                        <td>{{ jdate($trade->created_at)->format('H:m:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <x-alerts.danger>ملکی فروخته نشده است</x-alerts.danger>
    @endif
</div>
