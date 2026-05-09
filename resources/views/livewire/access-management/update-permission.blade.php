<div>
    <x-modal id="update-permission-modal-{{ $permission->id }}" title="ویرایش دسترسی">
        <x-form.input wire:model="title" name="title" label="عنوان دسترسی" />
        <x-form.input wire:model="name" name="name" label="نام دسترسی" />

        <p>مسئولیت های دارای این دسترسی:</p>

        @if ($permission->roles->count() > 0)
            <ul class="list-group">
                @foreach ($permission->roles as $permissionRole)
                    <li>
                        <span>{{ $permissionRole->title }}</span>
                        <x-button color="danger" wire:confirm="آیا می خواهید این مسیولیت را حذف کیند؟"
                            wire:click="deleteRole({{ $permissionRole->id }})">
                            حذف</x-button>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
        @endif

        <p class="modal-text">به کدام مسئولیت ها این دسترسی را اضافه می کنید؟</p>

        @forelse ($roles as $role)
            @unless ($role->name == 'super-admin')
                <div class="input-group">
                    <input class="normal" value="{{ $role->name }}" wire:model="addedRoles" type="checkbox"
                        id="permission-{{ $role->id }}">
                    <label for="permission-{{ $role->id }}">{{ $role->title }}</label>
                </div>
            @endunless
        @empty
            <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
        @endforelse

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
