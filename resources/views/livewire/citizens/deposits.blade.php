<div>
    <x-forms.search-box wire:model.debounce.1500="searchTerm"></x-forms.search-box>
    @if (count($payments) > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام کاربر</th>
                <th>مبلغ تراکنش</th>
                <th>شماره مرجع بانک</th>
                <td>شماره کارت یا حساب مبدا</td>
                <td>نام درگاه</td>
                <td>محصول خریداری شده</td>
                <td>تاریخ واریز</td>
                <td>ساعت واریز</td>
            </x-slot:headers>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $payment->user->name }}</td>
                    <td>{{ $payment->amount }}</td>
                    <td>{{ $payment->ref_id }}</td>
                    <td>{{ $payment->card_pan }}</td>
                    <td>{{ $payment->gateway }}</td>
                    <td>{{ $payment->getTitle() }}</td>
                    <td>{{ jdate($payment->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($payment->created_at)->format('h:m:s') }}</td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $payments->links() }}
    @else
        <x-alerts.danger>تراکنشی یافت نشد</x-alerts.danger>
    @endif
</div>
