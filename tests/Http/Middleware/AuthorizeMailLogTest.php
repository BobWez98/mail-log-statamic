<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Tests\Http\Middleware;

use BobWez98\MailLogStatamic\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Role as FileRole;
use Statamic\Auth\File\User as FileUser;
use Statamic\Facades\Role;
use Statamic\Facades\User;

final class AuthorizeMailLogTest extends TestCase
{
    #[Test]
    public function it_forbids_users_without_the_mail_log_permission(): void
    {
        $mailLog = $this->createMailLog();
        $role = Role::make('cp-only');
        $this->assertInstanceOf(FileRole::class, $role);
        $role->permissions(['access cp']);
        $role->save();

        $user = User::make();
        $this->assertInstanceOf(FileUser::class, $user);
        $user->id('user-without-mail-log');
        $user->email('without-mail-log@example.com');
        $user->assignRole($role);
        $user->save();

        $this->actingAs($user);

        $this->get($this->cpRoute('mail-log-statamic.index'))->assertForbidden();
        $this->get($this->cpRoute('mail-log-statamic.data'))->assertForbidden();
        $this->get($this->cpRoute('mail-log-statamic.show', $mailLog->id))->assertForbidden();
        $this->get($this->cpRoute('mail-log-statamic.preview', $mailLog->id))->assertForbidden();
    }

    #[Test]
    public function it_allows_users_with_the_mail_log_permission(): void
    {
        $mailLog = $this->createMailLog();
        $role = Role::make('mail-log-viewer');
        $this->assertInstanceOf(FileRole::class, $role);
        $role->permissions([
            'access cp',
            'view mail log',
        ]);
        $role->save();

        $user = User::make();
        $this->assertInstanceOf(FileUser::class, $user);
        $user->id('mail-log-viewer');
        $user->email('mail-log-viewer@example.com');
        $user->assignRole($role);
        $user->save();

        $this->actingAs($user);

        $this->get($this->cpRoute('mail-log-statamic.index'))->assertOk();
        $this->get($this->cpRoute('mail-log-statamic.show', $mailLog->id))->assertOk();
    }
}
