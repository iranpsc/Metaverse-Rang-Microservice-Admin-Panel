<?php

namespace Tests\Unit\Notifications;

use App\Notifications\AccountCreatedNotification;
use App\Notifications\KycDeniedNotification;
use App\Notifications\TicketResponded;
use Illuminate\Notifications\Messages\MailMessage;
use Kavenegar\Laravel\Message\KavenegarMessage;
use Tests\TestCase;

class NotificationCoverageTest extends TestCase
{
    public function test_account_created_notification_builds_kavenegar_message(): void
    {
        $notification = new AccountCreatedNotification('admin@example.com', 'secret', 123456);

        $this->assertSame(['kavenegar'], $notification->via((object) []));

        $message = $notification->toKavenegar((object) []);

        $this->assertInstanceOf(KavenegarMessage::class, $message);
    }

    public function test_kyc_denied_notification_builds_mail_and_array_payload(): void
    {
        $notification = new KycDeniedNotification('KYC rejected');

        $this->assertSame(['database'], $notification->via((object) []));
        $this->assertInstanceOf(MailMessage::class, $notification->toMail((object) []));
        $this->assertSame([
            'related-to' => 'kyc',
            'sender-image' => 'https://dl.qzparadise.ir/public/metarang/logo.png',
            'sender-name' => 'متارنگ',
            'message' => 'KYC rejected',
        ], $notification->toArray((object) []));
    }

    public function test_ticket_responded_notification_builds_mail_and_array_payload(): void
    {
        $notification = new TicketResponded('Ticket reply body');

        $this->assertSame(['database'], $notification->via((object) []));
        $this->assertInstanceOf(MailMessage::class, $notification->toMail((object) []));
        $this->assertSame(['message' => 'Ticket reply body'], $notification->toArray((object) []));
    }
}
