<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Data;

use BobWez98\MailLog\Data\Data;
use BobWez98\MailLog\Models\MailLog;

/**
 * @property int $id
 * @property string $messageId
 * @property string $status
 * @property string $from
 * @property string $to
 * @property string $subject
 * @property array<string, mixed> $data
 * @property string $createdAt
 * @property string|null $sentAt
 * @property string $previewUrl
 *
 * @extends Data<string, mixed>
 */
class StatamicMailLog extends Data
{
    protected array $rules = [
        'id' => 'required|integer',
        'messageId' => 'required|uuid',
        'status' => 'required|string',
        'from' => 'required|string',
        'to' => 'present|string',
        'subject' => 'present|string',
        'data' => 'present|array',
        'createdAt' => 'required|date',
        'sentAt' => 'present|nullable|date',
        'previewUrl' => 'required|string',
    ];

    public static function new(MailLog $mailLog): static
    {
        return static::make([
            'id' => $mailLog->id,
            'messageId' => $mailLog->message_id,
            'status' => $mailLog->status->value,
            'from' => $mailLog->from,
            'to' => $mailLog->to,
            'subject' => $mailLog->subject,
            'data' => $mailLog->data,
            'createdAt' => $mailLog->created_at->toIso8601String(),
            'sentAt' => $mailLog->sent_at?->toIso8601String(),
            'previewUrl' => cp_route('mail-log-statamic.preview', $mailLog->id),
        ])->validate();
    }
}
