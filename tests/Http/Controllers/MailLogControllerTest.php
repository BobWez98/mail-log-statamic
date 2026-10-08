<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Tests\Http\Controllers;

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLogStatamic\Tests\TestCase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

final class MailLogControllerTest extends TestCase
{
    #[Test]
    public function it_shows_the_mail_log_index(): void
    {
        $this->signInAsSuperUser();

        $this
            ->get($this->cpRoute('mail-log-statamic.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('mail-log-statamic::Index')
                ->where('listingUrl', $this->cpRoute('mail-log-statamic.data')));
    }

    #[Test]
    public function it_returns_paginated_mail_logs_newest_first(): void
    {
        $this->signInAsSuperUser();

        $oldest = $this->createMailLog([
            'subject' => 'Oldest message',
            'created_at' => Carbon::parse('2026-10-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);
        $newest = $this->createMailLog([
            'subject' => 'Newest message',
            'created_at' => Carbon::parse('2026-10-02 10:00:00'),
            'updated_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);

        $this
            ->getJson($this->cpRoute('mail-log-statamic.data', ['page' => 1, 'perPage' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.0.subject', 'Newest message')
            ->assertJsonPath('data.0.status', 'success')
            ->assertJsonPath('data.0.show_url', $this->cpRoute('mail-log-statamic.show', $newest->id))
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.columns.0.field', 'subject');

        $this
            ->getJson($this->cpRoute('mail-log-statamic.data', ['page' => 2, 'perPage' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.id', $oldest->id)
            ->assertJsonPath('meta.current_page', 2);
    }

    #[Test]
    public function it_shows_a_mail_log(): void
    {
        $this->signInAsSuperUser();

        $mailLog = $this->createMailLog([
            'status' => LogStatus::PENDING,
            'sent_at' => null,
        ]);

        $this
            ->get($this->cpRoute('mail-log-statamic.show', $mailLog->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('mail-log-statamic::Show')
                ->where('indexUrl', $this->cpRoute('mail-log-statamic.index'))
                ->where('mailLog.id', $mailLog->id)
                ->where('mailLog.messageId', $mailLog->message_id)
                ->where('mailLog.status', 'pending')
                ->where('mailLog.subject', 'Quarterly report')
                ->where('mailLog.sentAt', null)
                ->where('mailLog.previewUrl', $this->cpRoute('mail-log-statamic.preview', $mailLog->id)));
    }

    #[Test]
    public function it_shows_a_mail_log_without_a_subject(): void
    {
        $this->signInAsSuperUser();

        $mailLog = $this->createMailLog(['subject' => '']);

        $this
            ->get($this->cpRoute('mail-log-statamic.show', $mailLog->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('mail-log-statamic::Show')
                ->where('mailLog.id', $mailLog->id)
                ->where('mailLog.subject', ''));
    }

    #[Test]
    public function it_shows_a_mail_log_without_a_recipient(): void
    {
        $this->signInAsSuperUser();

        $mailLog = $this->createMailLog(['to' => '']);

        $this
            ->get($this->cpRoute('mail-log-statamic.show', $mailLog->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('mail-log-statamic::Show')
                ->where('mailLog.id', $mailLog->id)
                ->where('mailLog.to', ''));
    }

    #[Test]
    public function it_returns_a_sandboxed_mail_preview(): void
    {
        $this->signInAsSuperUser();

        $mailLog = $this->createMailLog([
            'body' => '<html><body><h1>Safe preview</h1><script>alert(1)</script></body></html>',
        ]);

        $this
            ->get($this->cpRoute('mail-log-statamic.preview', $mailLog->id))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'; img-src data: cid:; style-src 'unsafe-inline'; font-src data:; object-src 'none'; frame-src 'none'; form-action 'none'; base-uri 'none'; frame-ancestors 'self'")
            ->assertSee('<h1>Safe preview</h1>', false);
    }

    #[Test]
    public function it_returns_not_found_for_an_unknown_mail_log(): void
    {
        $this->signInAsSuperUser();

        $this
            ->get($this->cpRoute('mail-log-statamic.show', 999))
            ->assertNotFound();

        $this
            ->get($this->cpRoute('mail-log-statamic.preview', 999))
            ->assertNotFound();
    }
}
