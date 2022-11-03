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
        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                    ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="phoneVerification"/>
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <x-forms.group for="access_password-{{ $asset->id }}" label="رمز دسترسی">
            <x-forms.input id="access_password-{{ $asset->id }}" type="password" wire:model="access_password" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">بروزرسانی</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
