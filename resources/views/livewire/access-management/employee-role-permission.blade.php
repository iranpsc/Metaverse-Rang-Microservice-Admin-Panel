<div>
    @can('Assign-Permission-To-Role')
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-admin-modal">ایجاد کاربر
    </x-buttons.btn-primary>
    @endcan
    @if (session('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
        @can('Assign-Permission-To-Role')
    <x-modals.modal id="create-admin-modal" title="ایجاد کاربر">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="employee" label="انتخاب کاربر">
            <select wire:model="employee" id="employee" class="form-control rounded">
                <option selected>انتخاب کنید</option>
                @forelse ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname . ' ' . $employee->lname }}</option>
                @empty
                    <option disabled>کارمندی تعریف نشده است</option>
                @endforelse
            </select>
            @error('employee')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="password" label="رمز عبور">
            <x-forms.input wire:model="password" id="password" />
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="accessPassword" label="رمز دسترسی">
            <x-forms.input wire:model="accessPassword" id="accessPassword" />
            @error('accessPassword')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        @error('noRolesOrPermissionsSelected')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <p class="modal-text">کدام مسئولیت ها را به این کارمند می دهید؟</p>
        @forelse ($roles as $role)
            <div class="input-group">
                <input class="normal" value="{{ $role->id }}" wire:model="addedRoles" type="checkbox" id="employee-roles-{{$role->id}}">
                <label for="employee-roles-{{$role->id}}">{{ $role->title }}</label>
            </div>
        @empty
            <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
        @endforelse
{{--        <p class="modal-text">کدام دسترسی ها را به این کارمند می دهید؟</p>--}}
{{--        @forelse ($permissions as $permission)--}}
{{--            <div class="input-group">--}}
{{--                <input class="normal" value="{{ $permission->id }}" wire:model="addedPermissions" type="checkbox" id="employee-permissions-{{$permission->id}}">--}}
{{--                <label for="employee-permissions-{{$permission->id}}">{{ $permission->title }}</label>--}}
{{--            </div>--}}
{{--        @empty--}}
{{--            <x-alerts.danger>دسترسی تعریف نشده است!</x-alerts.danger>--}}
{{--        @endforelse--}}
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
        @endcan
    @if ($admins->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام کاربر</th>
                <th>مسئولیت ها</th>
                <th>دسترسی ها</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($admins as $admin)
                @php
                    if ($admin->hasRole('Super-Admin')) {
                        continue;
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $admin->name }}</td>
                    <td>{{ json_decode($admin->getRoleTitles())[0] ?? "" }}</td>
                    <td>{{ json_decode($admin->permissions->pluck('title'))[0] ?? "" }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($admin->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($admin->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" id="{{ $admin->id }}" title="deleteAdmin">حذف
                        </x-buttons.btn-danger>
                        <x-buttons.btn-primary data-bs-target="#update-admin-modal-{{$admin->id}}" data-bs-toggle="modal">بروزرسانی
                        </x-buttons.btn-primary>
                        <livewire:access-management.update-admin :admin="$admin" :wire:key="'update-admin-'.$admin->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $admins->links() }}
    @else
        <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
    @endif
</div>
