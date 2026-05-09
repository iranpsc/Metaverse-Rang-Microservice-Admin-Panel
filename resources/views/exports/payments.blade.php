<table>
    <thead>
        <tr>
            <th>شناسه</th>
            <th>نام کاربر</th>
            <th>مبلغ تراکنش(ریال)</th>
            <th>شماره مرجع بانک</th>
            <td>شماره کارت یا حساب مبدا</td>
            <td>نام درگاه</td>
            <td>محصول خریداری شده</td>
            <td>تاریخ و ساعت واریز</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->user->name }}</td>
                <td>{{ number_format($payment->amount, 0) }}</td>
                <td>{{ $payment->ref_id }}</td>
                <td>{{ $payment->card_pan }}</td>
                <td>{{ $payment->gateway }}</td>
                <td>{{ $payment->getTitle() }}</td>
                <td>{{ jdate($payment->created_at)->format('Y/m/d H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
