<div>
    <x-forms.search-box wire:model="searchTerm"/>

    @if ($users->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام کاربری</th>
                <th>ایمیل</th>
                <th>تاریخ وریفای ایمیل</th>
                <th>آی پی ثبت نام</th>
            </x-slot:headers>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($user->email_verified_at)->format('Y/m/d') }}</td>
                    <td>{{ $user->ip }}</td>
                <tr>
            @endforeach
        </x-tables.table>
        {{ $users->links() }}
    @else
        <x-alerts.danger>کاربری یافت نشد</x-alerts.danger>
    @endif
</div>
