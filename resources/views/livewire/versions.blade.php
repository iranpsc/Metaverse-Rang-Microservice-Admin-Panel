<div>
    <x-button data-bs-toggle="modal" data-bs-target="#modal" class="mb-2">
        <span class="fa fa-plus"></span>
        تعریف ورژن
    </x-button>

    <x-modal size="modal-xl" id="modal" title="تعریف ورژن">

        <x-form.input name="versionTitle" label="شناسه نسخه" />

        <x-form.input name="title" label="عنوان" />

        <div class="form-group">
            <label for="content">متن</label>
            <div wire:ignore>
                <textarea id="content" class="form-control"></textarea>
            </div>
            @error('content')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="date" name="startsAt" label="تاریخ شروع" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button id="save-btn">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modal>

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
                <tr wire:key="{{ $version->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $version->title }}</td>
                    <td>{{ Str::limit($version->content, 20) }}</td>
                    <td>{{ $version->version_title }}</td>
                    <td>{{ jdate($version->starts_at)->format('Y/m/d') }}</td>
                    <td>{{ $version->views->count() }}</td>
                    <td>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟"
                            wire:click="delete({{ $version->id }})">
                            <span class="fa fa-trash"></span>
                            حذف
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $versions->links() }}
    @else
        <x-alert type="warning" message="ورژنی یافت نشد!" />
    @endif
</div>

@script
    <script>
        let content = CKEDITOR.replace('content');
        let saveBtn = document.getElementById('save-btn');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('content', content.getData());
            $wire.call('save');
        });
    </script>
@endscript
