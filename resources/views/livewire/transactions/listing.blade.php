<div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th><i class="icon-energy"></i></th>
                    <th>نوع تراکنش</th>
                    <th>تاریخ</th>
                    <th>ارز</th>
                    <th>مبلغ</th>
                    <th>کاربر</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ $transaction->action }}
                        </td>
                        <td>{{  \Morilog\Jalali\Jalalian::forge($transaction->created_at)->format('Y/m/d') }}</td>
                        <td>{{ $transaction->currency }}</td>
                        <td>{{ $transaction->amount }}</td>
                        <td>{{ $transaction->user->name }}</td>
                        <td class="text-center">
                            @switch($transaction->status)
                                @case(1)
                                <label class="label-success round">تائید شده</label>

                                @break
                                @case(0)
                                <label class="label-warning round">در انتظار پرداخت</label>

                                @break
                                @case(-1)

                                <label class="label-danger round">رد شده</label>
                                    @break
                                @default

                            @endswitch
                        </td>
                    </tr>
                @empty
                <x-alert :type="'danger'" :message="'هیچ تراکنشی یافت نشد'"></x-alert>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
