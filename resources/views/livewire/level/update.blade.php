<div>
    <x-modal id="edit-level-modal-{{ $level->id }}" title="بروزرسانی سطح">
        <x-form.input name="name" label="نام سطح" />

        <x-form.input name="slug" label="نامک" />

        <x-form.input type="file" name="image" label="تصویر" />

        <x-form.input type="file" name="background_image" label="تصویر پس زمینه" />

        <x-form.input name="score" label="امتیاز" />

        <x-form.verification/>

        <x-slot:footer>
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
