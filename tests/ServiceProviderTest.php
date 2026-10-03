<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Tests;

use BobWez98\MailLogStatamic\ServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Permission as RegisteredPermission;
use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;

final class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_package_services(): void
    {
        $this->signInAsSuperUser();

        $this->assertSame([], config('mail-log-statamic'));
        $this->assertContains(config_path('mail-log-statamic.php'), array_values(ServiceProvider::pathsToPublish(ServiceProvider::class, 'config')));

        $permission = Permission::boot()->get('view mail log');

        $this->assertInstanceOf(RegisteredPermission::class, $permission);
        $this->assertSame('View mail log', $permission->label());

        $mailLogNavigation = Nav::build()
            ->pluck('items')
            ->flatten()
            ->first(static fn (mixed $item): bool => $item instanceof NavItem && $item->display() === 'Mail Log');

        $this->assertInstanceOf(NavItem::class, $mailLogNavigation);
        $this->assertSame($this->cpRoute('mail-log-statamic.index'), $mailLogNavigation->url());
    }
}
