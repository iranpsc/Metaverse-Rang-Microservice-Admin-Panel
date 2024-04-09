<div>

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-role-modal">
        ایجاد مسئولیت
    </x-button>

    <x-modal id="create-role-modal" size="modal-xl" title="ایجاد مسئولیت">
        <x-form.input wire:model="title" name="title" label="عنوان مسئولیت" />
        <x-form.input wire:model="name" name="name" label="نام مسئولیت" />

        <p class="modal-text">کدام دسترسی ها را به این مسئولیت می دهید؟</p>

        @forelse ($permissions->chunk(4) as $permission)
            <div class="row">
                @foreach ($permission as $item)
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input class="normal" value="{{ $item->name }}" wire:model="addedPermissions"
                                type="checkbox" id="role-permissions-{{ $item->id }}">
                            <label for="role-permissions-{{ $item->id }}">{{ $item->title }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <x-alert type="danger" :message="'مسئولیتی تعریف نشده است'" />
        @endforelse

        <x-slot name="footer">
            <x-button color="success" wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>

    </x-modals.modal>

    @if ($roles->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان مسئولیت</th>
                <th>نام مسئولیت</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($roles as $role)
                <tr wire:key="{{ $role->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $role->title }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ jdate($role->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($role->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-button color="danger" wire:confirm="آیا می خواهید این مسیولیت را حذف کنید؟"
                            wire:click="delete({{ $role->id }})">
                            حذف
                        </x-button>
                        <x-button color="info" data-bs-target="#update-role-modal-{{ $role->id }}" data-bs-toggle="modal">
                            بروزرسانی
                        </x-button>
                        <livewire:access-management.update-role :role="$role" :wire:key="'update-role-'.$role->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $roles->links() }}
    @else
        <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
    @endif
</div>
