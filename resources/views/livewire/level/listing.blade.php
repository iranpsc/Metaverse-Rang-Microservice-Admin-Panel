<div>
    <x-button class="mb-2" data-bs-toggle="modal" data-bs-target="#create-level">تعریف سطح</x-button>

    <x-modal id="create-level" title="تعریف سطح">
        <x-form.input name="name" label="نام سطح" />

        <x-form.input name="slug" label="نامک" />

        <x-form.input type="file" name="image" label="تصویر" />

        <x-form.input type="file" name="background_image" label="تصویر پس زمینه" />


        <x-form.input name="score" label="امتیاز" />

        <x-form.verification />

        <x-slot:footer>
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot:footer>
    </x-modals.modal>

    @if ($levels->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نام سطح</th>
                <th>امتیاز مورد نیاز</th>
                <th>اسلاگ</th>
                <th>تصویر</th>
                <th>تصویر پس زمینه</th>
                <th>اقدامات</th>
            </x-slot>
            @foreach ($levels as $level)
                <tr wire:key="{{ $level->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $level->name }}</td>
                    <td>{{ $level->score }}</td>
                    <td>{{ $level->slug }}</td>
                    <td>
                        <a target="_blank" href="{{ 'uploads/' . optional($level->image)->url }}">مشاهده</a>
                    </td>
                    <td>
                        <a target="_blank" href="{{ $level->background_image }}">مشاهده</a>
                    </td>
                    <td>
                        <x-button data-bs-target="#level-info-modal-{{ $level->id }}" data-bs-toggle="modal">اطلاعات
                            سطح</x-button>

                        <x-button data-bs-target="#edit-level-modal-{{ $level->id }}"
                            data-bs-toggle="modal">ویرایش</x-button>

                        <x-button color="danger" wire:confirm="آیا می خواهید این سطح را حذف کنید؟"
                            wire:click="delete({{ $level->id }})">حذف</x-button>

                        <livewire:level.update :level="$level" :wire:key="'update-'.$level->id" />

                        <x-modal id="level-info-modal-{{ $level->id }}" title="اطلاعات سطح"
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
                                    <a href="#prize-{{ $level->id }}" data-bs-toggle="tab" class="nav-link">پاداش
                                        رسیدن به سطح</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general-info-{{ $level->id }}">
                                    <livewire:level.info.general-info :$level :key="$level->id" />
                                </div>
                                <div class="tab-pane fade show" id="licences-{{ $level->id }}">
                                    <livewire:level.info.licenses :$level :key="$level->id" />
                                </div>
                                <div class="tab-pane fade show" id="gem-{{ $level->id }}">
                                    <livewire:level.info.gem :$level :key="$level->id" />
                                </div>
                                <div class="tab-pane fade show" id="gift-{{ $level->id }}">
                                    <livewire:level.info.gift :$level :key="$level->id" />
                                </div>
                                <div class="tab-pane fade show" id="prize-{{ $level->id }}">
                                    <livewire:level.info.prize :$level :key="$level->id" />
                                </div>
                            </div>
                        </x-modals.modal>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $levels->links() }}
    @else
        <x-alert type="warning" :message="'سطحی تعریف نشده است'" />
    @endif
</div>
