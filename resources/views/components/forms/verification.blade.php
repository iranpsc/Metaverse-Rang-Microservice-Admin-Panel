
@props([
    'id' => '1'
])
<div class="row form-group">
    <div class="col-sm-4">
        <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
            ارسال پیامک تایید
        </x-buttons.btn-success>
    </div>
    <div class="col-sm-8">
        <x-forms.input wire:model="phone_verification" placeholder="تایید پیامکی" />
        @error('phone_verification')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>

</div>

<x-forms.group label="رمز دسترسی" for="access_password_{{ $id }}">
    <x-forms.input type="password" id="access_password_{{ $id }}" wire:model="access_password"
        placeholder="رمز دسترسی" />
    @error('access_password')
        <span class="form-text text-danger">{{ $message }}</span>
    @enderror
</x-forms.group>
