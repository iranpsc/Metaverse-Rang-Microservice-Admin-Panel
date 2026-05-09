<div>
    <x-form.search-box wire:model.live="searchTerm" />

    <button type="button" class="btn btn-primary btn-sm round my-2" wire:click="export">دانلود خروجی اکسل</button>

    @if (count($payments) > 0)
        <x-table>
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
                <tr wire:key="{{ $payment->id }}">
                    <td>{{ $payment->id }}</td>
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
        </x-table>
        {{ $payments->links() }}
    @else
        <x-alert type="warning" message="تراکنشی یافت نشد." />
    @endif
</div>
