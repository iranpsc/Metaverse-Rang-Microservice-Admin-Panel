<div>
    @can('Define-Level')
        <x-buttons.btn-success class="mb-2" data-bs-toggle="modal" data-bs-target="#create-level">تعریف سطح
        </x-buttons.btn-success>
        @livewire('level.create', key('create-level'))
    @endcan
    @can('Show-Level')
        @if ($levels->count() > 0)
            <x-tables.table>
                <x-slot name="headers">
                    <th>نام سطح</th>
                    <th>امتیاز مورد نیاز</th>
                    <th>اسلاگ</th>
                    <th>تصویر</th>
                    <th>اقدامات</th>
                </x-slot>
                @foreach ($levels as $level)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->score }}</td>
                        <td>{{ $level->slug }}</td>
                        <td>
                            @can('Show-Level')
                                @if ($level->image)
                                    <a target="_blank" href="{{ asset('uploads/' . $level->image->url) }}"
                                        class="btn btn-primary btn-sm round">مشاهده</a>
                                @else
                                    -----
                                @endif
                            @endcan
                        </td>
                        <td>
                            @can('Define-Level-Prizes')
                                @if (!empty($level->prize))
                                    <x-buttons.btn-primary data-bs-target="#edit-prizes-modal-{{ $level->id }}"
                                        data-bs-toggle="modal">ویرایش جوایز
                                    </x-buttons.btn-primary>
                                @else
                                    <x-buttons.btn-primary data-bs-target="#create-prizes-modal-{{ $level->id }}"
                                        data-bs-toggle="modal">تعریف جوایز
                                    </x-buttons.btn-primary>
                                @endif
                            @endcan
                            @can('Edit-Level')
                                <x-buttons.btn-success data-bs-target="#edit-level-modal-{{ $level->id }}"
                                    data-bs-toggle="modal">ویرایش
                                </x-buttons.btn-success>
                            @endcan
                            @can('Delete-Level')
                                <x-buttons.btn-danger class="confirm" id="{{ $level->id }}" title="deleteLevel">حذف
                                </x-buttons.btn-danger>
                            @endcan
                            @can('Edit-Level')
                                <livewire:level.update :level="$level" :wire:key="'update-'.$level->id">
                                @endcan
                                <livewire:level.create-prize :level="$level" :wire:key="'create-prize-'.$level->id">
                                    @if (!empty($level->prize))
                                        <livewire:level.edit-prize :level="$level" :wire:key="'edit-prize-'.$level->id">
                                    @endif
                        </td>
                    </tr>
                @endforeach
            </x-tables.table>
            {{ $levels->links() }}
        @else
            <x-alerts.danger>سطحی تعریف نشده است</x-alerts.danger>
        @endif
    @endcan
</div>
