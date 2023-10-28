<div>
    <x-slot name="pageTitle">
        مدیریت کارمندان
    </x-slot>

    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#create-admin-modal">
        ایجاد کاربر
    </x-button>

    <x-forms.search-box wire:model="search"/>

    <x-modals.modal id="create-admin-modal" title="ایجاد کاربر">
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

        @error('roles')
            <span class="text-danger">{{ $message }}</span>
        @enderror

        <p class="modal-text">کدام مسئولیت ها را به این کارمند می دهید؟</p>
        @forelse ($defined_roles as $role)
            <div class="input-group">
                <input class="normal" value="{{ $role->id }}" wire:model="roles" type="checkbox" id="employee-roles-{{$role->id}}">
                <label for="employee-roles-{{$role->id}}">{{ $role->title }}</label>
            </div>
        @empty
            <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
        @endforelse

        <x-forms.verification/>

        <x-slot name="footer">
            <x-button color="success" wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
    @if ($admins->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نام کاربر</th>
                <th>مسئولیت ها</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($admins as $key => $admin)
                @php
                    if ($admin->hasRole('super-admin')) {
                        continue;
                    }
                @endphp
                <tr>
                    <td>{{ $admin->id }}</td>
                    <td>{{ $admin->name }}</td>
                    <td>{{ implode('، ', json_decode($admin->getRoleTitles())) }}</td>
                    <td>{{ jdate($admin->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($admin->created_at)->format('H:m:s') }}</td>
                    <td>
                        @unless ($admin->id == Auth::user()->id)
                            <x-button color="danger" class="confirm" id="{{ $admin->id }}" title="deleteAdmin">
                                حذف
                            </x-button>
                        @endunless
                        <x-button color="primary" data-bs-target="#update-admin-modal-{{$admin->id}}" data-bs-toggle="modal">
                            ویرایش
                        </x-button>
                        <livewire:access-management.update-admin :admin="$admin" :wire:key="'update-admin-'.$admin->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="warning" :message="'کاربری تعریف نشده است'"/>
    @endif
</div>
