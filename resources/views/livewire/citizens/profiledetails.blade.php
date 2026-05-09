<div>
    <x-form.search-box wire:model="searchTerm"/>

    @if ($users->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>شناسه شهروند</th>
                <th>تاریخ و ساعت ثبت نام</th>
                <th>کل زمان حضور</th>
                <th>تعداد مشترکین</th>
                <th>خرید ابزار و PSC</th>
                <th>تعداد پرداخت های بالای ۱ میلیون تومان</th>
                <th>کل امتیاز دریافتی</th>
            </x-slot:headers>
            @foreach ($users as $user)
                <tr wire:key="{{ $user->id }}">
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->code }}</td>
                    <td>{{ jdate($user->created_at)->format('Y/m/d H:i:s') }}</td>
                    <td>{{ $user->activities_sum_total ?? 0 }}</td>
                    <td>{{ $user->followers_count }}</td>
                    <td>{{ $user->payments_count }}</td>
                    <td>{{ $user->more_than_a_million_payment }}</td>
                    <td>{{ $user->score }}</td>
                </tr>
            @endforeach
        </x-table>
        {{ $users->links() }}
    @else
        <x-alert type="warning" message="کاربری یافت نشد." />
    @endif
</div>
