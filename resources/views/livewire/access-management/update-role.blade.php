<div>
    <x-modal size="modal-xl" id="update-role-modal-{{ $role->id }}" title="ویرایش مسئولیت">
        <x-form.input wire:model="title" name="title" label="عنوان مسئولیت" />
        <x-form.input wire:model="name" name="name" label="نام مسئولیت" />
        <p>دسترسی های اختصاص داده شده به این مسئولیت:</p>

        @if ($role->permissions->count() > 0)
            <ul class="list-group">
                @foreach ($role->permissions as $rolePermissions)
                    <li>
                        <span>{{ $rolePermissions->title }}</span>
                        <x-button color="danger" wire:click="removePermission({{ $rolePermissions->id }})"
                            wire:confirm="آیا می خواهید این دسترسی را حذف کنید؟">حذف</x-button>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alert type="warning">دسترسی ای تعریف نشده است!</x-alert>
        @endif
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
            <x-alert type="warning">دسترسی ای تعریف نشده است!</x-alert>
        @endforelse
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
