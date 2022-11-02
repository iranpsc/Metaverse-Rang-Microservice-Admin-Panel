<div>
    <x-modals.modal id="modal-{{ explode('+', $feature->properties->id)[1] }}" title="ویرایش اطلاعات ملک">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

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

        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">ارسال پیامک تایید</x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="phoneVerification" placeholder="تایید پیامکی" />
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <div class="row form-group">
            <label for="access_password" class="form-col-label col-sm-4">رمز دسترسی</label>
            <div class="col-sm-8">
                <x-forms.input type="password" wire:model="access_password" placeholder="رمز دسترسی" />
                @error('access_password')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <x-slot:footer>
            <x-buttons.btn-primary wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
