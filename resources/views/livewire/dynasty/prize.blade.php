<div>
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-prize">تعریف جوایز</x-buttons.btn-primary>
    <x-create-dynasty-prize></x-create-dynasty-prize>

    @if ($prizes->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نسبت خانوادگی</th>
                <th>جزپیات</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($prizes as $prize)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \App\Helpers\getRelationTitle($prize->member) }}</td>
                    <td>
                        <button class="btn btn-primary round" data-bs-toggle="modal"
                            data-bs-target="#view-prize-{{ $prize->id }}">مشاهده</button>
                    </td>
                    <td>
                        <button class="btn btn-info round" data-bs-toggle="modal"
                            data-bs-target="#edit-prize-{{ $prize->id }}">ویرایش</button>
                        <button class="btn btn-danger round" wire:click="delete({{ $prize->id }})">حذف</button>
                    </td>
                </tr>
                @livewire('dynasty.edit-prize', ['prize' => $prize], key($prize->relationship . $prize->id))
                <x-dynasty-prize-listing :prize="$prize"></x-dynasty-prize-listing>
            @endforeach
        </x-tables.table>
        {{ $prizes->links() }}
    @else
        <x-alerts.danger>پاداشی تعریف نشده است</x-alerts.danger>
    @endif
</div>
