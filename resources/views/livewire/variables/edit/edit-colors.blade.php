<div>
    {{-- The Master doesn't talk, he acts. --}}
    <x-modals.modal id="edit-currency-modal-{{ $asset->id }}" title="ویرایش ارز">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <x-forms.group for="price" label="قیمت واحد">
            <x-forms.input type="text" wire:model="price" class="only-number" />
            @error('price')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <div class="form-group">
            <label for="note">علت بروزرسانی</label>
            <textarea name="note" id="note" cols="30" rows="3" class="form-control rounded" wire:model="note"></textarea>
        </div>
        <div class="row form-group">
            <div class="col-sm-4">
                <a href="javascript:void(0)" class="btn btn-success btn-block btn-sm rounded" wire:click="sendSMS">
                    ارسال پیامک تایید

                </a>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control rounded" wire:model="phoneVerification">
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <x-forms.group for="access_password" label="رمز دسترسی">
            <x-forms.input type="password" wire:model="access_password" class="only-number" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-primary wire:click="update">بروزرسانی</x-buttons.btn-primary>
        </x-slot:footer>
    </x-modals.modal>
</div>
