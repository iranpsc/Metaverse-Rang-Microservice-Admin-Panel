<div>
    <x-modals.modal id="edit-package-modal-{{ $option->id }}" title="بروزرسانی بسته">
        <x-forms.group label="تعداد" for="amount-{{ $option->id }}">
            <x-forms.input id="amount-{{ $option->id }}" wire:model="amount" placeholder="تعداد را وارد کنید" />
            @error('amount')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="کد بسته" for="code-{{ $option->id }}">
            <x-forms.input id="code-{{ $option->id }}" wire:model="code" placeholder="کد بسته را وارد کنید" />
            @error('code')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image-{{ $option->id }}">
            <x-forms.input type="file" id="image-{{ $option->id }}" wire:model="image" placeholder="تصویر بسته" />
            <x-progress-bar/>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="note-{{ $option->id }}" label="یادداشت">
            <textarea id="note-{{ $option->id }}" cols="30" rows="3" class="form-control rounded" placeholder="یادداشت"
                wire:model="note"></textarea>
        </x-forms.group>

        <x-forms.verification id="{{ $option->id }}"/>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
