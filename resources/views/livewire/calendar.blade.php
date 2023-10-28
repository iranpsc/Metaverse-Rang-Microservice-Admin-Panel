<div>
    <x-slot name="pageTitle">
        تقویم
    </x-slot>
    
    <x-button wire:click="openCreateModal" class="mb-2">
        <span class="fa fa-plus"></span>
    </x-button>

    <x-modal size="modal-xl" id="modal" title="ایجاد وقعه">

        <x-forms.group for="title" label="عنوان">
            <x-forms.input id="title" wire:model.defer="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="content" label="متن">
            <div wire:ignore>
                <textarea id="content"></textarea>
            </div>
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
            <x-progress-bar />
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="start_date" label="تاریخ شروع">
            <x-forms.input type="date" id="start_date" wire:model.defer="start_date" />
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
            <x-forms.input id="btn_name" wire:model.defer="btn_name" />
            @error('btn_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="btn_link" label="لینک دکمه ورود به واقعه">
            <x-forms.input id="btn_link" wire:model.defer="btn_link" />
            @error('btn_link')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        @production
            <x-forms.verification />
        @endproduction
    </x-modal>

    @if ($events->count() > 0)
        <x-table>
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
            </x-slot>

            @foreach ($events as $event)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $event->title }}</td>
                    <td>{{ Str::limit($event->content, 20) }}</td>
                    <td>{{ $event->color }}</td>
                    <td>{{ jdate($event->starts_at)->format('Y/m/d H:i:s') }}</td>
                    <td>{{ $event->ends_at ? jdate($event->ends_at)->format('Y/m/d H:i:s') : '' }}</td>
                    <td>
                        <a target="_blank" href="{{ $event->image }}">مشاهده</a>
                    </td>
                    <td>{{ $event->views->count() }}</td>
                    <td>{{ $event->interactions->where('liked', 1)->count() }}</td>
                    <td>{{ $event->interactions->where('liked', 0)->count() }}</td>
                    <td>{{ $event->getStatus() }}</td>
                    <td>
                        <x-button color="info" id="edit-btn-{{ $event->id }}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" id="delete-btn-{{ $event->id }}">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $events->links() }}
    @else
        <x-alert type="warning" message="وقعه ای یافت نشد!" />
    @endif

    <x-scripts/>
</div>
