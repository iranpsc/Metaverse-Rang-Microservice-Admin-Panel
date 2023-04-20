<div>
    {{-- Success is as dangerous as failure. --}}
    <x-modals.modal id="update-admin-modal-{{$admin->id}}" title="ویرایش دسترسی ها و مسئولیت های کارمند">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <p>مسئولیت های اختصاص داده شده به این کارمند:</p>
        @if ($admin->roles->count() > 0)
            <ul class="list-group">
                @foreach ($admin->roles as $role)
                    <li>
                        <span>{{ $role->title }}</span>
                        <x-buttons.btn-danger id="{{ $role->id }}" class="confirm" title="removeAdminRole">حذف</x-buttons.btn-danger>
                    </li>
                @endforeach
            </ul>
        @else
            <x-alerts.danger>هیچ مسئولیتی به این کارمند اختصاص داده نشده است!</x-alert.info>
        @endif
        <p class="modal-text">کدام مسئولیت ها را به این کارمند اضافه می کنید؟</p>
        @forelse ($roles as $role)
            @if ($role->name == 'super-admin')
                @continue
            @endif
            <div class="input-group">
                <input class="normal" value="{{ $role->id }}" wire:model="addedRoles" type="checkbox" id="update-admin-roles-{{$role->id}}">
                <label for="update-admin-roles-{{$role->id}}">{{ $role->title }}</label>
            </div>
        @empty
            <x-alerts.danger>مسئولیتی تعریف نشده است!</x-alerts.danger>
        @endforelse
        <x-forms.verification id="{{ $admin->id }}"/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>

