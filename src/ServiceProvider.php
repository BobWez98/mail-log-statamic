<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic;

use Override;
use Statamic\Auth\Permission;
use Statamic\CP\Navigation\Nav as Navigation;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission as PermissionFacade;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    #[Override]
    public function bootAddon(): void
    {
        $this
            ->bootPermissions()
            ->bootNavigation();
    }

    protected function bootPermissions(): static
    {
        PermissionFacade::group('mail-log', __('Mail Log'), function (): void {
            PermissionFacade::register('view mail log', function (Permission $permission): void {
                $permission
                    ->label(__('View mail log'))
                    ->description(__('Gives the user access to view logged emails.'));
            });
        });

        return $this;
    }

    protected function bootNavigation(): static
    {
        Nav::extend(function (Navigation $nav): void {
            $item = $nav->create(__('Mail Log'));
            $item->section(__('Tools'));
            $item->route('mail-log-statamic.index');
            $item->can('view mail log');
            $item->icon('mail');
        });

        return $this;
    }
}
