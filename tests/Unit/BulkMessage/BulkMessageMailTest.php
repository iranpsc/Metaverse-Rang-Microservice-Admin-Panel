<?php

namespace Tests\Unit\BulkMessage;

use App\Mail\BulkMessageMail;
use Tests\TestCase;

class BulkMessageMailTest extends TestCase
{
    public function test_mailable_has_html_and_plain_text_parts(): void
    {
        $mailable = new BulkMessageMail('<p>Hello</p>', 'Hello');

        $content = $mailable->content();

        $this->assertSame('<p>Hello</p>', $content->htmlString);
        $this->assertSame('mail.bulk-message-text', $content->text);
    }

    public function test_mailable_includes_list_unsubscribe_header(): void
    {
        $mailable = new BulkMessageMail('<p>Hi</p>', 'Hi', 'https://example.com/unsubscribe');

        $headers = $mailable->headers();

        $this->assertArrayHasKey('List-Unsubscribe', $headers->text);
        $this->assertArrayHasKey('List-Unsubscribe-Post', $headers->text);
        $this->assertStringContainsString('https://example.com/unsubscribe', $headers->text['List-Unsubscribe']);
    }
}
