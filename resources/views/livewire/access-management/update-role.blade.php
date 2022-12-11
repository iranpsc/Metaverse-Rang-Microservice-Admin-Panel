<div>
    {{-- Success is as dangerous as failure. --}}
    <x-modals.modal id="update-role-modal-{{$role->id}}" title="ویرایش مسئولیت">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="title-{{ $role->id }}" label="عنوان مسئولیت">
            <x-forms.input wire:model="title" id="title-{{ $role->id }}" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="name-{{ $role->id }}" label="نام مسئولیت">
            <x-forms.input type="name" wire:model="name" id="name-{{ $role->id }}" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <p>دسترسی های اختصاص داده شده به این مسئولیت:</p>

        @if ($role->permissions->count() > 0)
            <ul class="list-group">
                @foreach ($role->permissions as $rolePermissions)
                    <li>
                        <span>{{ $rolePermissions->title }}</span>
                        <x-buttons.btn-danger id="{{ $rolePermissions->id }}" class="confirm" title="removeRolePermission">حذف</x-buttons.btn-danger>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alerts.danger>هیچ دسترسی به این مسئولیت اختصاص داده نشده است!</x-alert.info>
        @endif
        <p class="modal-text">کدام دسترسی ها را به این مسئولیت می دهید؟</p>
        @forelse ($permissions as $permission)
            <div class="input-group">
                <input class="normal" value="{{ $permission->id }}" wire:model="addedPermissions" type="checkbox" id="role-permissions-{{$permission->id}}">
                <label for="role-permissions-{{$permission->id}}">{{ $permission->title }}</label>
            </div>
        @empty
            <x-alerts.danger>نقشی تعریف نشده است!</x-alerts.danger>
        @endforelse
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>

