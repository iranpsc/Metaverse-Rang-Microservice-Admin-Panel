<div>
    <x-modal id="update-admin-modal-{{ $adminUser->id }}" title="ویرایش دسترسی ها و مسئولیت های کارمند">
        <p>مسئولیت های اختصاص داده شده به این کارمند:</p>
        @if ($adminUser->roles->count() > 0)
            <ul class="list-group">
                @foreach ($adminUser->roles as $role)
                    <li>
                        <span>{{ $role->title }}</span>
                        <x-button color="danger" wire:confirm="آیا می خواهید این مسیولیت را حذف کنید؟"
                            wire:click="deleteRole({{ $role->id }})">حذف</x-buttons.btn-danger>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alert type="info" :message="'هیچ مسئولیتی به این کارمند اختصاص داده نشده است!'" />
        @endif
        <p class="modal-text">کدام مسئولیت ها را به این کارمند اضافه می کنید؟</p>
        @forelse ($roles as $role)
            @if ($role->name == 'super-admin')
                @continue
            @endif
            <div class="input-group">
                <input class="normal" value="{{ $role->id }}" wire:model="addedRoles" type="checkbox"
                    id="update-admin-roles-{{ $role->id }}">
                <label for="update-admin-roles-{{ $role->id }}">{{ $role->title }}</label>
            </div>
        @empty
            <x-alert type="warning" :message="'مسئولیتی تعریف نشده است'" />
        @endforelse

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>
</div>
