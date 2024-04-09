<div>
    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#create-permission-modal">
        ایجاد دسترسی
    </x-button>

    <x-modal id="create-permission-modal" title="ایجاد دسترسی">
        <x-form.input name="title" label="عنوان دسترسی" />

        <x-form.input name="name" label="نام دسترسی" />

        <p>به کدام مسئولیت ها این دسترسی را می دهید؟</p>

        <div class="container-fluid">
            <div class="row">
                @forelse ($roles as $role)
                    <div class="col-md-3">
                        @if ($role->name !== 'super-admin')
                            <div class="input-group">
                                <input class="normal" value="{{ $role->name }}" wire:model="addedRoles"
                                    type="checkbox" id="role-{{ $role->id }}">
                                <label for="role-{{ $role->id }}">{{ $role->title }}</label>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-12">
                        <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
                    </div>
                @endforelse
            </div>
        </div>
        <x-slot:footer>
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>

    @if ($permissions->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان دسترسی</th>
                <th>نام دسترسی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($permissions as $permission)
                <tr wire:key="{{ $permission->id }}" >
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $permission->title }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>{{ jdate($permission->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($permission->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-button data-bs-target="#update-permission-modal-{{ $permission->id }}"
                            data-bs-toggle="modal">بروزرسانی
                        </x-button>
                        <x-button color="danger" wire:click="delete({{ $permission->id }})"
                            wire:confirm="آیا می خواهید این دسترسی را حذف کنید؟">حذف
                        </x-button>
                        <livewire:access-management.update-permission :permission="$permission"
                            :wire:key="'update-permission-'.$permission->id" />
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $permissions->links() }}
    @else
        <x-alert type="warning" :message="'دسترسی تعریف نشده است'" />
    @endif
</div>
