<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Http\Resources;

use BobWez98\MailLog\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MailLog */
class MailLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'status' => $this->status->value,
            'created_at' => $this->created_at->toIso8601String(),
            'show_url' => cp_route('mail-log-statamic.show', $this->id),
        ];
    }
}
