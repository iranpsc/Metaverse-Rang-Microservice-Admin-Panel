<div>
    @if (count($ips) > 0)
        <x-table>
            <x-slot name="headers">
                <th>ایمیل درخواست کننده</th>
                <th>آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ips as $ip)
                <tr wire:key="{{ $ip->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ip->email }}</td>
                    <td>{{ $ip->from }}</td>
                    <td>{{ jdate($ip->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($ip->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-button wire:confirm="ایا می خواهید تایید کنید؟" wire:click="approve({{ $ip->id }})">
                            <span class="fa fa-check"></span>
                            تایید
                        </x-button>
                        <x-button color="danger" wire:confirm="آیا می خواهید رد کنید؟" wire:click="deny({{ $ip->id }})">
                            <span class="fa fa-times"></span>
                            رد
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $ips->links() }}
    @else
    <x-alert type="info" message="هیچ آی پی ای برای نمایش وجود ندارد." />
    @endif
</div>
