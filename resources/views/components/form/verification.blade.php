@php
    $id = Str::random(10);
@endphp

<div class="row form-group">
    <div class="col-sm-6">
        <div class="row">
            <div class="col-sm-4" wire:ignore>
                <button type="button" class="btn btn-sm btn-success sms-btn rounded"
                    wire:click="sendSMS('{{ $id }}')" wire:loading.attr="disabled" wire:target="sendSMS"
                    id="{{ $id }}">
                    <span wire:loading.remove wire:target="sendSMS">ارسال کد تایید</span>
                    <span wire:loading wire:target="sendSMS">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        در حال ارسال
                    </span>
                </button>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control rounded" wire:model="phone_verification"
                    placeholder="تایید پیامکی" />
                @error('phone_verification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <x-form.input type="password" name="access_password" label="رمز دسترسی" />
    </div>
</div>
