@php
    $id = Str::random(10);
@endphp

<div class="row form-group">
    <div class="col-sm-4" wire:ignore>
        <x-buttons.btn-success class="sms-btn" wire:click="sendSMS('{{ $id }}')" wire:loading.attr="disabled" wire:target="sendSMS" id="{{ $id }}">
            <span wire:loading.remove wire:target="sendSMS">ارسال کد تایید</span>
            <span wire:loading wire:target="sendSMS">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                در حال ارسال
            </span>
        </x-buttons.btn-success>
    </div>
    <div class="col-sm-8">
        <x-forms.input wire:model.defer="phone_verification" placeholder="تایید پیامکی" />
        @error('phone_verification')
            <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<x-forms.group label="رمز دسترسی" for="access-password">
    <x-forms.input type="password" id="access-password" wire:model.defer="access_password" placeholder="رمز دسترسی" />
    @error('access_password')
        <span class="form-text text-danger">{{ $message }}</span>
    @enderror
</x-forms.group>
