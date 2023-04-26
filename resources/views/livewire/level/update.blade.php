<div>
    <x-modals.modal id="edit-level-modal-{{ $level->id }}" title="بروزرسانی سطح">
        <x-forms.group for="name-{{ $level->id }}" label="نام سطح">
            <x-forms.input id="name{{ $level->id }}" wire:model="name" />
            @error('name')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image-{{ $level->id }}">
            <x-forms.input type="file" id="image-{{ $level->id }}" wire:model="image" />
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر پس زمینه" for="backgroundImage-{{ $level->id }}">
            <x-forms.input type="file" id="backgroundImage-{{ $level->id }}" wire:model="backgroundImage" />
            <span class="text-success" wire:loading wire:target="backgroundImage">در حال بارگذاری ...</span>
            @error('backgroundImage')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="score-{{ $level->id }}" label="امتیاز مورد نیاز">
            <x-forms.input id="score{{ $level->id }}" wire:model="score" />
            @error('score')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot:footer>
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
