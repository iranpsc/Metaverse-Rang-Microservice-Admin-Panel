<div>
    {{-- Success is as dangerous as failure. --}}
    <x-modals.modal id="update-permission-modal-{{ $permission->id }}" title="ویرایش دسترسی">
        <x-forms.group for="permission-title-{{ $permission->id }}" label="عنوان دسترسی">
            <x-forms.input wire:model="title" id="permission-title-{{ $permission->id }}" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="permission-name-{{ $permission->id }}" label="نام دسترسی">
            <x-forms.input wire:model="name" id="permission-name-{{ $permission->id }}" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <p>مسئولیت های دارای این دسترسی:</p>

        @if ($permission->roles->count() > 0)
            <ul class="list-group">
                @foreach ($permission->roles as $permissionRole)
                    <li>
                        <span>{{ $permissionRole->title }}</span>
                        <x-buttons.btn-danger id="{{ $permissionRole->id }}" class="confirm" title="removePermissionRole">
                            حذف</x-buttons.btn-danger>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alerts.danger>این دسترسی به هیچ مسئولیتی داده نشده است!</x-alert.info>
        @endif
        <p class="modal-text">به کدام مسئولیت ها این دسترسی را اضافه می کنید؟</p>
        @forelse ($roles as $role)
            @unless($role->name == 'super-admin')
                <div class="input-group">
                    <input class="normal" value="{{ $role->id }}" wire:model="addedRoles" type="checkbox"
                        id="permission-{{ $role->id }}">
                    <label for="permission-{{ $role->id }}">{{ $role->title }}</label>
                </div>
            @endunless
        @empty
            <x-alerts.danger>نقشی تعریف نشده است!</x-alerts.danger>
        @endforelse
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
