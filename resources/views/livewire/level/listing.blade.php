    <div>
        <x-buttons.btn-success class="mb-2" data-bs-toggle="modal" data-bs-target="#create-level">تعریف سطح
        </x-buttons.btn-success>
        @livewire('level.create', key('create-level'))
        @if ($levels->count() > 0)
            <x-tables.table>
                <x-slot name="headers">
                    <th>نام سطح</th>
                    <th>امتیاز مورد نیاز</th>
                    <th>اسلاگ</th>
                    <th>تصویر</th>
                    <th>اقدامات</th>
                </x-slot>
                @foreach ($levels as $key => $level)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->score }}</td>
                        <td>{{ $level->slug }}</td>
                        <td>
                            @if ($level->image)
                                <a target="_blank" href="{{ $level->image?->url }}" class="btn btn-primary btn-sm round">مشاهده</a>
                            @else
                                -----
                            @endif
                        </td>
                        <td>
                            @if (!empty($level->prize))
                                <x-buttons.btn-primary
                                    data-bs-target="#edit-prizes-modal-{{ $key }}"
                                    data-bs-toggle="modal">ویرایش جوایز</x-buttons.btn-primary>
                            @else
                                <x-buttons.btn-primary data-bs-target="#create-prizes-modal-{{ $key }}"
                                    data-bs-toggle="modal">تعریف جوایز</x-buttons.btn-primary>
                            @endif
                            <x-buttons.btn-success data-bs-target="#edit-level-modal-{{ $key }}"
                                data-bs-toggle="modal">ویرایش</x-buttons.btn-success>
                            <x-buttons.btn-danger class="confirm" id="{{ $level->id }}" title="deleteLevel">حذف
                            </x-buttons.btn-danger>
                            @livewire('level.update', ['level' => $level, 'key' => $key], key('update-' . $key))
                            @livewire('level.create-prize', ['level' => $level, 'key' => $key], key('create-prize-' . $key))
                            @if (!empty($level->prize))
                                @livewire('level.edit-prize', ['level' => $level, 'key' => $key], key('edit-prize-' . $key))
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-tables.table>
            {{ $levels->links() }}
        @else
            <x-alerts.danger>سطحی تعریف نشده است</x-alerts.danger>
        @endif
    </div>

