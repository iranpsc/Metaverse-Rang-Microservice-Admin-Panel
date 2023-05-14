<div>
    <x-buttons.btn-success class="mb-2" data-bs-toggle="modal" data-bs-target="#create-level">تعریف سطح</x-buttons.btn-success>
    @livewire('level.create', key('create-level'))
    @if ($levels->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام سطح</th>
                <th>امتیاز مورد نیاز</th>
                <th>اسلاگ</th>
                <th>تصویر</th>
                <th>تصویر پس زمینه</th>
                <th>اقدامات</th>
            </x-slot>
            @foreach ($levels as $level)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $level->name }}</td>
                    <td>{{ $level->score }}</td>
                    <td>{{ $level->slug }}</td>
                    <td>
                        @if ($level->image)
                            <a target="_blank" href="{{ asset('uploads/' . $level->image->url) }}"
                                class="btn btn-primary btn-sm round">مشاهده</a>
                        @else
                            -----
                        @endif
                    </td>
                    <td>
                        <x-buttons.btn-link target="_blank" link="{{ $level->background_image }}">مشاهده</x-buttons.btn-link>
                    </td>
                    <td>
                        <x-buttons.btn-primary data-bs-target="#level-info-modal-{{ $level->id }}"
                            data-bs-toggle="modal">اطلاعات سطح</x-buttons.btn-primary>
                        <x-buttons.btn-success data-bs-target="#edit-level-modal-{{ $level->id }}"
                            data-bs-toggle="modal">ویرایش</x-buttons.btn-success>
                        <x-buttons.btn-danger class="confirm" id="{{ $level->id }}" title="deleteLevel">حذف
                        </x-buttons.btn-danger>

                        <livewire:level.update :level="$level" :wire:key="'update-'.$level->id">

                        <x-modals.modal id="level-info-modal-{{ $level->id }}" title="اطلاعات سطح"
                            size="modal-xl modal-fullscreen">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a href="#general-info-{{ $level->id }}" data-bs-toggle="tab"
                                        class="nav-link active">اطلاعات اولیه</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#licences-{{ $level->id }}" data-bs-toggle="tab"
                                        class="nav-link">مجوزها و دسترسی ها</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#gem-{{ $level->id }}" data-bs-toggle="tab" class="nav-link">نگین
                                        سطح</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#gift-{{ $level->id }}" data-bs-toggle="tab" class="nav-link">هدیه
                                        سطح</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#prize-{{ $level->id }}" data-bs-toggle="tab"
                                        class="nav-link">پاداش رسیدن به سطح</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general-info-{{ $level->id }}">
                                    <livewire:level.info.general-info :level="$level" :key="'level-' . $level->id">
                                </div>
                                <div class="tab-pane fade show" id="licences-{{ $level->id }}">
                                    <livewire:level.info.licenses :level="$level" :key="'level-' . $level->id">
                                </div>
                                <div class="tab-pane fade show" id="gem-{{ $level->id }}">
                                    <livewire:level.info.gem :level="$level" :key="'level-' . $level->id">
                                </div>
                                <div class="tab-pane fade show" id="gift-{{ $level->id }}">
                                    <livewire:level.info.gift :level="$level" :key="'level-' . $level->id">
                                </div>
                                <div class="tab-pane fade show" id="prize-{{ $level->id }}">
                                    <livewire:level.info.prize :level="$level" :key="'level-' . $level->id">
                                </div>
                            </div>
                        </x-modals.modal>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $levels->links() }}
    @else
        <x-alerts.danger>سطحی تعریف نشده است</x-alerts.danger>
    @endif
</div>
