<div>
    <x-slot name="pageTitle">
        لیست ورژن ها
    </x-slot>
    
    <x-button wire:click="openCreateModal" class="mb-2">
        <span class="fa fa-plus"></span>
    </x-button>

    @if ($versions->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>متن</th>
                <th>نسخه ورژن</th>
                <th>تاریخ شروع</th>
                <th>تعداد بازدید</th>
                <th>اقدامات</th>
            </x-slot>

            @foreach ($versions as $version)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $version->title }}</td>
                    <td>{{ Str::limit($version->content, 20) }}</td>
                    <td>{{ $version->version_title }}</td>
                    <td>{{ jdate($version->starts_at)->format('Y/m/d') }}</td>
                    <td>{{ $version->views->count() }}</td>
                    <td>
                        <x-button id="edit-btn-{{ $version->id }}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" id="delete-btn-{{ $version->id }}">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $versions->links() }}
    @else
        <x-alert type="warning" message="ورژنی یافت نشد!" />
    @endif

    <x-modal size="modal-xl" id="modal" title="تعریف ورژن">

        <x-forms.group for="versionTitle" label="شناسه نسخه">
            <x-forms.input id="versionTitle" wire:model.defer="versionTitle" placeholder="V1.0.1.1" />
            @error('versionTitle')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

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

        <x-forms.group for="startsAt" label="تاریخ شروع">
            <x-forms.input type="date" id="startsAt" wire:model.defer="startsAt" />
            @error('startsAt')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        @production
            <x-forms.verification />
        @endproduction

    </x-modal>

    <x-scripts/>
</div>
