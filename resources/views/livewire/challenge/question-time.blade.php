<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-tables.table>
        <x-slot name="headers">
            <th>عنوان</th>
            <th>زمان عنوان </th>
            <th>تنظیمات</th>
        </x-slot>
        @foreach ($times as $time)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $time->key }}</td>
                <td>{{ $time->value }}</td>
                <td>
                    <x-buttons.btn-success data-bs-target="#update-time-{{$time->id}}" data-bs-toggle="modal" >ویرایش</x-buttons.btn-success>

                </td>
            </tr>
            @livewire('challenge.update-time', ['time' => $time], key('time-'.$time->id))
        @endforeach
    </x-tables.table>
</div>
