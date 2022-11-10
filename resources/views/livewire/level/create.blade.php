<div>
    <x-modals.modal id="create-level" title="تعریف سطح">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name" label="نام سطح">
            <x-forms.input id="name" wire:model="name" />
            @error('name')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="slug" label="اسلاگ">
            <x-forms.input id="slug" wire:model="slug" />
            @error('slug')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="score" label="امتیاز مورد نیاز">
            <x-forms.input id="score" wire:model="score" />
            @error('score')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot:footer>
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
