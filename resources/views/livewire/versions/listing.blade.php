<div>
    <x-buttons.btn-primary wire:click="resetModal" class="mb-2" data-bs-toggle="modal" data-bs-target="#version-modal">تعریف ورژن</x-buttons.btn-primary>

    @if ($versions->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>متن</th>
                <th>نسخه ورژن</th>
                <th>تاریخ شروع</th>
                <th>تعداد بازدید</th>
                <th>لایک</th>
                <th>دیسلایک</th>
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
                    <td>{{ $version->interactions->where('liked', 1)->count() }}</td>
                    <td>{{ $version->interactions->where('liked', 0)->count() }}</td>
                    <td>
                        <x-buttons.btn-primary id="edit-btn-{{ $version->id }}">
                            <span class="fa fa-edit"></span>
                        </x-buttons.btn-primary>
                        <x-buttons.btn-danger id="delete-btn-{{ $version->id }}">
                            <span class="fa fa-trash"></span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>ورژنی ثبت نشده است.</x-alerts.danger>
    @endif

    <x-modals.modal size="modal-xl" id="version-modal" title="تعریف ورژن">

        <div id="version-title-group">
            <x-forms.group for="versionTitle" label="شناسه نسخه">
                <x-forms.input id="versionTitle" wire:model="versionTitle" placeholder="V1.0.1.1"/>
                @error('versionTitle')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <x-forms.group for="title" label="عنوان">
            <x-forms.input id="title" wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="description" label="متن">
            <div wire:ignore>
                <textarea id="description"></textarea>
            </div>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="startsAt" label="تاریخ شروع">
            <x-forms.input type="date" id="startsAt" wire:model="startsAt" />
            @error('startsAt')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification />

        <x-slot name="footer">
            <x-buttons.btn-primary id="store-btn">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
            var action = 'store';
            let versionId = null;
            var description = CKEDITOR.replace('description');
            var storeBtn = document.getElementById('store-btn');
            var modal = new bootstrap.Modal(document.getElementById('version-modal'), {
                keyboard: false
            });

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            storeBtn.addEventListener('click', function() {
                @this.set('description', description.getData());

                if(action == 'store') {
                    @this.call('store');
                } else if(action == 'update') {
                    @this.call('update', versionId);
                }
                action = 'store';
            });

            @foreach ($versions as $version)
                var deleteBtn{{ $version->id }} = document.getElementById('delete-btn-{{ $version->id }}');
                deleteBtn{{ $version->id }}.addEventListener('click', function() {
                    Swal.fire({
                        title: 'آیا از حذف این ورژن مطمئن هستید؟',
                        text: "این عمل غیر قابل بازگشت می باشد.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'بله',
                        cancelButtonText: 'خیر'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('delete', {{ $version->id }});
                        }
                    })
                });

                var editBtn{{ $version->id }} = document.getElementById('edit-btn-{{ $version->id }}');
                editBtn{{ $version->id }}.addEventListener('click', function() {
                    @this.call('edit', {{ $version->id }});
                });
            @endforeach

            window.addEventListener('openEditModal', event => {
                versionId = event.detail.id;
                description.setData(event.detail.description);
                modal.show();
                action = 'update';
            });
        })
    </script>
</div>
