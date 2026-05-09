<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Kavenegar\Laravel\Notification\KavenegarBaseNotification;
use Kavenegar\Laravel\Message\KavenegarMessage;

class SendVerificationCode extends KavenegarBaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $code;

    public function __construct()
    {
        $this->code = random_int(100000, 999999);

        Cache::put(
            'verify.code.' . Auth::guard('admin')->id(),
            Hash::make($this->code),
            now()->addMinutes(1)
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['kavenegar'];
    }

    /**
     * Get the Kavenegar / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return KavenegarMessage
     */
    public function toKavenegar($notifiable)
    {
        return (new KavenegarMessage())
            ->verifyLookup('verify', $this->code);
    }
}
