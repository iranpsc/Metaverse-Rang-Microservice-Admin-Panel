<div>
    @if (count($ips) > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>ایمیل درخواست کننده</th>
                <th>آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ips as $ip)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ip->email }}</td>
                    <td>{{ $ip->from }}</td>
                    <td>{{ jdate($ip->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($ip->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-primary id="approve-btn-{{ $ip->id }}">
                            <span class="fa fa-check"></span>
                        </x-buttons.btn-primary>
                        <x-buttons.btn-danger id="deny-btn-{{ $ip->id }}">
                            <span class="fa fa-times"></span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $ips->links() }}
    @else
        <x-alerts.danger>درخواستی ثبت نشده است.</x-alerts.danger>
    @endif

    <script>
        window.addEventListener('livewire:load', function() {
            @foreach ($ips as $ip)
                $('#approve-btn-{{ $ip->id }}').click(function() {
                    Swal.fire({
                        title: 'آیا از تایید این آی پی مطمئن هستید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'خیر',
                        confirmButtonText: 'بله'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('approve', {{ $ip->id }})
                        }
                    });
                });

                $('#deny-btn-{{ $ip->id }}').click(function() {
                    Swal.fire({
                        title: 'آیا از رد این آی پی مطمئن هستید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'خیر',
                        confirmButtonText: 'بله'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deny', {{ $ip->id }})
                        }
                    });
                });
            @endforeach
        });
    </script>
</div>
