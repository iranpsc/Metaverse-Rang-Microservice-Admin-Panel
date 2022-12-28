<div>
    @can('Define-Permission')
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-permission-modal">ایجاد دسترسی
    </x-buttons.btn-primary>
    @endcan
    @if (session('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
        @can('Define-Permission')
    <x-modals.modal id="create-permission-modal" title="ایجاد دسترسی">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="title" label="عنوان دسترسی">
            <x-forms.input type="title" wire:model="title" id="title"/>
            @error('title')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="name" label="نام دسترسی">
            <x-forms.input wire:model="name" id="name"/>
            @error('name')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <p>به کدام مسئولیت ها این دسترسی را می دهید؟</p>
        @forelse ($roles as $role)
            @if ($role->name !== 'Super-Admin')
                <div class="input-group">
                    <input class="normal" value="{{ $role->id }}" wire:model="addedRoles" type="checkbox"
                           id="role-{{ $role->id }}">
                    <label for="role-{{ $role->id }}">{{ $role->title }}</label>
                </div>
            @endif
        @empty
            <x-alerts.danger>نقشی تعریف نشده است!</x-alerts.danger>
        @endforelse
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
        @endcan
    @if ($permissions->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان دسترسی</th>
                <th>نام دسترسی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($permissions as $permission)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $permission->title }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($permission->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($permission->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" id="{{ $permission->id }}" title="deletePermission">حذف
                        </x-buttons.btn-danger>
                        <x-buttons.btn-primary data-bs-target="#update-permission-modal-{{ $permission->id }}"
                                               data-bs-toggle="modal">بروزرسانی
                        </x-buttons.btn-primary>
                        <livewire:access-management.update-permission :permission="$permission"
                                                                      :wire:key="'update-permission-'.$permission->id"/>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $permissions->links() }}
    @else
        <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
    @endif
</div>
