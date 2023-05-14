<div>
    <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#create-event-modal">ایجاد وقعه</x-buttons.btn-primary>

    <x-modals.modal size="modal-xl" id="create-event-modal" title="ایجاد وقعه">

        <x-forms.group for="title" label="عنوان">
            <x-forms.input id="title" wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="content" label="متن">
            <textarea id="content" wire:model="content" rows="10" cols="20" class="form-control rounded">
            </textarea>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="color" label="رنگ">
            <x-forms.input type="color" id="color" wire:model="color" />
            @error('color')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="image" label="تصویر">
            <x-forms.input type="file" id="image" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_date" label="تاریخ شروع">
            <x-forms.input type="date" id="start_date" wire:model="start_date" />
            @error('start_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_date" label="تاریخ پایان">
            <x-forms.input type="date" id="end_date" wire:model="end_date" />
            @error('end_date')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_time" label="ساعت شروع">
            <x-forms.input type="time" id="start_time" wire:model="start_time" />
            @error('start_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="end_time" label="ساعت پایان">
            <x-forms.input type="time" id="end_time" wire:model="end_time" />
            @error('end_time')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="btn_name" label="نام دکمه ورود به واقعه">
            <x-forms.input id="btn_name" wire:model="btn_name" />
            @error('btn_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="btn_link" label="لینک دکمه ورود به واقعه">
            <x-forms.input id="btn_link" wire:model="btn_link" />
            @error('btn_link')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="input-group my-2">
            <input class="normal" wire:model="is_version" type="checkbox" id="is_version">
            <label for="is_version">این واقعه ورژن است.</label>
        </div>

        <x-forms.group for="version_title" label="شناسه نسخه">
            <x-forms.input id="version_title" wire:model="version_title" />
            @error('version_title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($events->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>متن</th>
                <th>رنگ</th>
                <th>زمان شروع</th>
                <th>زمان پایان</th>
                <th>تصویر</th>
                <th>تعداد بازدید</th>
                <th>لایک</th>
                <th>دیسلایک</th>
                <th>وضعیت</th>
                <th>اقدامات</th>

                @foreach ($events as $event)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $event->title }}</td>
                        <td>{{ Str::limit($event->content, 20) }}</td>
                        <td>{{ $event->color }}</td>
                        <td>{{ jdate($event->starts_at)->format('Y/m/d H:i:s') }}</td>
                        <td>{{ jdate($event->ends_at)->format('Y/m/d H:i:s') }}</td>
                        <td><x-buttons.btn-link target="_blank" link="{{ $event->image }}">مشاهده</x-buttons.btn-link></td>
                        <td>{{ $event->views->count() }}</td>
                        <td>{{ $event->interactions->where('liked', 1)->count() }}</td>
                        <td>{{ $event->interactions->where('liked', 0)->count() }}</td>
                        <td>{{ $event->getStatus() }}</td>
                        <td>
                            <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#edit-event-modal-{{$event->id}}">ویرایش</x-buttons.btn-primary>
                            <x-buttons.btn-danger class="confirm" id="{{ $event->id }}" title="deleteEvent">حذف</x-buttons.btn-danger>
                            <livewire:calendar.update :event="$event" :key="'event-'.$event->id" />
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-tables.table>
    @else
        <x-alerts.danger>وقعه ای ثبت نشده است.</x-alerts.danger>
    @endif
</div>
