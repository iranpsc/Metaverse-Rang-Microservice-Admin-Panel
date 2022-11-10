<div>
    <x-modals.modal id="edit-level-modal-{{ $key }}" title="بروزرسانی سطح">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name-{{ $key++ }}" label="نام سطح">
            <x-forms.input id="name{{ $key++ }}" wire:model="name" />
            @error('name')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="score-{{ $key++ }}" label="امتیاز مورد نیاز">
            <x-forms.input id="score{{ $key++ }}" wire:model="score" />
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
