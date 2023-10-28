<div>
    <x-slot name="pageTitle">
        {{ __('اطلاعات ثبت نام') }}
    </x-slot>
    
    <x-forms.search-box wire:model="searchTerm"/>

    @if ($users->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام کاربری</th>
                <th>ایمیل</th>
                <th>تاریخ وریفای ایمیل</th>
                <th>آی پی ثبت نام</th>
            </x-slot:headers>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ jdate($user->email_verified_at)->format('Y/m/d') }}</td>
                    <td>{{ $user->ip }}</td>
                <tr>
            @endforeach
        </x-table>
        {{ $users->links() }}
    @else
        <x-alert type="warning" :message="'کاربری تعریف نشده است'"/>
    @endif
</div>
