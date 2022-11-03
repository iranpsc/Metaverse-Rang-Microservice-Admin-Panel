<div>
    <x-modals.modal id="edit-package-modal-{{ $option->id }}" title="بروزرسانی بسته">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        <x-forms.group label="تعداد" for="amount-{{ $option->id }}">
            <x-forms.input id="amount-{{ $option->id }}" wire:model="amount" placeholder="تعداد را وارد کنید" />
            @error('amount')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="note-{{ $option->id }}" label="یادداشت">
            <textarea id="note-{{ $option->id }}" cols="30" rows="3" class="form-control rounded" placeholder="یادداشت"
                wire:model="note"></textarea>
        </x-forms.group>

        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                    ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="phoneVerification" placeholder="تایید پیامکی" />
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <x-forms.group label="رمز دسترسی" for="edit-option-access-password-{{ $option->id }}">
            <x-forms.input type="password" id="edit-option-access-password-{{ $option->id }}"
                wire:model="access_password" placeholder="رمز دسترسی" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
