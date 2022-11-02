<div>
    <x-modals.modal id="edit-message-{{ $message->id }}" title="ویرایش پیام">
        @if(session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="content" label="متن پیام">
            <textarea class="form-control form-control-sm rounded" wire:model.lazy="content" rows="10"></textarea>
            @error('content')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:click="edit">ذخیره</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
