<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Tests\Data;

use BobWez98\MailLogStatamic\Data\StatamicMailLog;
use BobWez98\MailLogStatamic\Tests\TestCase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;

final class StatamicMailLogTest extends TestCase
{
    #[Test]
    public function it_maps_a_mail_log_for_statamic(): void
    {
        $createdAt = Carbon::parse('2026-10-03 10:00:00');
        $sentAt = Carbon::parse('2026-10-03 10:00:05');
        $mailLog = $this->createMailLog([
            'created_at' => $createdAt,
            'updated_at' => $sentAt,
            'sent_at' => $sentAt,
        ]);

        $data = StatamicMailLog::new($mailLog);

        $this->assertSame([
            'id' => $mailLog->id,
            'messageId' => $mailLog->message_id,
            'status' => 'success',
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'data' => ['campaign' => 'quarterly'],
            'createdAt' => $createdAt->toIso8601String(),
            'sentAt' => $sentAt->toIso8601String(),
            'previewUrl' => $this->cpRoute('mail-log-statamic.preview', $mailLog->id),
        ], $data->toArray());
        $this->assertSame($data, $data->validate());
    }
}
