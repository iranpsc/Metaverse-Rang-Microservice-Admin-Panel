<div>
    @can('Define-Role')
        <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-role-modal">ایجاد مسئولیت
        </x-buttons.btn-primary>
    @endcan
    @if (session('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
    @can('Define-Role')
        <x-modals.modal id="create-role-modal" title="ایجاد مسئولیت">
            @if (session('success'))
                <x-alerts.success>{{ session('success') }}</x-alerts.success>
            @endif
            <x-forms.group for="title" label="عنوان مسئولیت">
                <x-forms.input type="title" wire:model="title" id="title"/>
                @error('title')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <x-forms.group for="name" label="نام مسئولیت">
                <x-forms.input wire:model="name" id="name"/>
                @error('name')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
            <p class="modal-text">کدام دسترسی ها را به این مسئولیت می دهید؟</p>
            @forelse ($permissions as $permission)
                <div class="input-group">
                    <input class="normal" value="{{ $permission->id }}" wire:model="addedPermissions" type="checkbox"
                           id="role-permissions-{{$permission->id}}">
                    <label for="role-permissions-{{$permission->id}}">{{ $permission->title }}</label>
                </div>
            @empty
                <x-alerts.danger>نقشی تعریف نشده است!</x-alerts.danger>
            @endforelse
            <x-slot name="footer">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            </x-slot>
        </x-modals.modal>
    @endcan
    @if ($roles->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان مسئولیت</th>
                <th>نام مسئولیت</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $role->title }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($role->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($role->created_at)->format('H:m:s') }}</td>
                    <td>
                        @can('Delete-Role')
                            <x-buttons.btn-danger class="confirm" id="{{ $role->id }}" title="deleteRole">حذف
                            </x-buttons.btn-danger>
                        @endcan
                        <x-buttons.btn-primary data-bs-target="#update-role-modal-{{$role->id}}" data-bs-toggle="modal">
                            بروزرسانی
                        </x-buttons.btn-primary>
                        <livewire:access-management.update-role :role="$role" :wire:key="'update-role-'.$role->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $roles->links() }}
    @else
        <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
    @endif
</div>
