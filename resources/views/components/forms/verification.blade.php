@props([
    'id' => Str::random(10),
])

<div class="row form-group">
    <div class="col-sm-4">
        <x-buttons.btn-success wire:click="sendSMS('send-sms-btn-{{ $id }}')" wire:loading.attr="disabled"
            wire:target="sendSMS" id="send-sms-btn-{{ $id }}">
            <span wire:loading.remove>ارسال کد تایید</span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                در حال ارسال ...
            </span>
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

@pushOnce('js')
    <script>
        window.addEventListener('start-countdown', (event) => {
            const sendSMSBtn = document.getElementById(event.detail.id);
            let countdownIntervalId;
            const countdownTime = event.detail.countdownTime; // 2 minutes in seconds

            // Disable the button and change its text to the countdown
            sendSMSBtn.disabled = true;
            sendSMSBtn.innerText = `ارسال مجدد بعد از ${countdownTime} ثانیه`;

            // Start the countdown interval
            let remainingTime = countdownTime;
            countdownIntervalId = setInterval(() => {
                remainingTime -= 1;
                sendSMSBtn.innerText = `ارسال مجدد بعد از ${remainingTime} ثانیه`;

                // When the countdown is finished, re-enable the button
                if (remainingTime === 0) {
                    clearInterval(countdownIntervalId);
                    sendSMSBtn.disabled = false;
                    sendSMSBtn.innerText = 'ارسال کد تایید';
                }
            }, 1000);
        });
    </script>
@endpushOnce
