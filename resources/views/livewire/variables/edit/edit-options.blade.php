<div>
    <x-modals.modal id="edit-package-modal-{{ $option->id }}" title="بروزرسانی بسته">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        <x-forms.group label="تعداد" for="package-color">
            <x-forms.input type="text" id="package-color" wire:model="amount" placeholder="تعداد را وارد کنید"
                class="only-number" />
            @error('amount')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="form-group">
            <label for="note">یادداشت</label>
            <textarea id="note" cols="30" rows="3" class="form-control rounded" placeholder="یادداشت" wire:model="note"></textarea>
        </div>

        <div class="row form-group">
            <div class="col-sm-4">
                <a href="javascript:void(0)" class="btn btn-success btn-block btn-sm rounded" wire:click="sendSMS">
                    ارسال پیامک تایید
                </a>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control rounded only-number" wire:model="phoneVerification"
                    placeholder="تایید پیامکی">
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <x-forms.group label="رمز دسترسی" for="access_password">
            <x-forms.input type="password" id="access_password" wire:model="access_password" placeholder="رمز دسترسی"
                class="only-number" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-primary wire:click="update">ثبت</x-buttons.btn-primary>
        </x-slot:footer>
    </x-modals.modal>
</div>
