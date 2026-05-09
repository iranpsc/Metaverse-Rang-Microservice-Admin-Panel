<div>
    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#create-admin-modal">
        ایجاد کاربر
    </x-button>

    <x-modal id="create-admin-modal" title="ایجاد کاربر">
        <div class="form-group row">
            <label for="employee" class="col-md-4 col-form-label text-md-right">انتخاب کارمند</label>
            <div class="col-md-8">
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
            </div>
        </div>
        @error('roles')
            <span class="text-danger">{{ $message }}</span>
        @enderror

        <p class="modal-text">کدام مسئولیت ها را به این کارمند می دهید؟</p>
        @forelse ($defined_roles->chunk(3) as $rolesChunk)
            <div class="row form-group">
                @foreach ($rolesChunk as $role)
                    <div class="col-md-4">
                        <div class="input-group">
                            <input class="normal" value="{{ $role->id }}" wire:model="roles" type="checkbox"
                                id="employee-roles-{{ $role->id }}">
                            <label for="employee-roles-{{ $role->id }}">{{ $role->title }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
        @endforelse

        @production
            <x-form.verification />
        @endproduction

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
                <tr wire:key="{{ $admin->id }}">
                    <td>{{ $admin->id }}</td>
                    <td>{{ $admin->name }}</td>
                    <td>{{ implode('، ', json_decode($admin->getRoleTitles())) }}</td>
                    <td>{{ jdate($admin->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($admin->created_at)->format('H:m:s') }}</td>
                    <td>
                        @unless ($admin->id == Auth::user()->id)
                            <x-button color="danger" wire:confirm="آیا می خواهید این کاربر را حذف کنید؟" wire:click="delete({{ $admin->id }})">
                                حذف
                            </x-button>
                        @endunless
                        <x-button color="primary" data-bs-target="#update-admin-modal-{{ $admin->id }}"
                            data-bs-toggle="modal">
                            ویرایش
                        </x-button>
                        <livewire:access-management.update-admin :adminUser="$admin" :key="'update-admin-'.$admin->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="warning" :message="'کاربری تعریف نشده است'" />
    @endif
</div>
