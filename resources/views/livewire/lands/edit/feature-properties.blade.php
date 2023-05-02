<div>
    <x-modals.modal id="modal-{{ explode('-', $feature->properties->id)[1] }}" title="ویرایش اطلاعات ملک">
        <x-forms.group for="area-{{ $feature->id }}" label="مساحت">
            <x-forms.input id="area-{{ $feature->id }}" wire:model="area" />
            @error('area')
                <span class="text-danger form-text">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="density-{{ $feature->id }}" label="تراکم">
            <x-forms.input id="density-{{ $feature->id }}" wire:model="density" />
            @error('density')
                <span class="text-danger form-text">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="karbari-{{ $feature->id }}" label="نوع کاربری">
            <x-forms.input id="karbari-{{ $feature->id }}" wire:model="karbari" />
            @error('karbari')
                <span class="text-danger form-text">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="address-{{ $feature->id }}" label="آدرس">
            <x-forms.input id="address-{{ $feature->id }}" wire:model="address" />
            @error('address')
                <span class="text-danger form-text">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="rgb-{{ $feature->id }}" label="قیمت گذاری">
            <x-forms.input id="rgb-{{ $feature->id }}" wire:model="rgb" />
            @error('rgb')
                <span class="text-danger form-text">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.verfification/>
        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
