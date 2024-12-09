<div>
    {{-- The whole world belongs to you. --}}
    <x-modal id="edit-modal-{{ $modal->id }}" title="ویرایش بخش">
        <x-form.input name="editingModalName" label="نام بخش" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="updateModal">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>
</div>
