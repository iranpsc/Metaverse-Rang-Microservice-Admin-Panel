<div>
    <x-modals.modal id="modal-{{ $feature->id }}" title="ویرایش مختصات ملک" size="modal-lg modal-dialog-scrollable">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if ($errors->any())
            <x-alerts.danger>ورودی ها را کنترل کنید</x-alerts.danger>
        @endif
        @foreach ($coordinates as $key => $coordinate)
            <div class="row">
                <div class="col-sm-6">
                    <x-forms.group for="x-{{ $db_coordinates[$key]->id }}" label="X">
                        <x-forms.input id="x-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.x" />
                    </x-forms.group>
                </div>
                <div class="col-sm-6">
                    <x-forms.group for="y-{{ $db_coordinates[$key]->id }}" label="Y">
                        <x-forms.input id="y-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.y" />
                    </x-forms.group>
                </div>
            </div>
        @endforeach

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
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
