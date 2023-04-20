<div>
    {{-- The Master doesn't talk, he acts. --}}
    <x-modals.modal id="edit-currency-modal-{{ $asset->id }}" title="ویرایش ارز">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <x-forms.group for="price-{{ $asset->id }}" label="قیمت واحد">
            <x-forms.input id="price-{{ $asset->id }}" wire:model="price" />
            @error('price')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="note-{{ $asset->id }}" label="علت بروزرسانی">
            <textarea id="note-{{ $asset->id }}" id="note" cols="30" rows="3" class="form-control rounded"
                wire:model="note"></textarea>
        </x-forms.group>

        <x-forms.verification id="{{ $asset->id }}"/>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">بروزرسانی</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
