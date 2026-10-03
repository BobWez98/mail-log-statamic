<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Tests;

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLog\ServiceProvider as MailLogServiceProvider;
use BobWez98\MailLogStatamic\ServiceProvider;
use Facades\Statamic\Version;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Rebing\GraphQL\GraphQLServiceProvider;
use Statamic\Addons\Manifest;
use Statamic\Auth\File\User as FileUser;
use Statamic\Facades\Path;
use Statamic\Facades\User;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use UnexpectedValueException;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
    use PreventsSavingStacheItemsToDisk;

    protected string $fakeStacheDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMix();
        $this->withoutVite();

        $this->fakeStacheDirectory = Path::resolve(__DIR__.'/__fixtures__/dev-null');
        $this->preventSavingStacheItemsToDisk();

        Version::shouldReceive('get')->andReturn('6.35.0');
    }

    protected function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    #[\Override]
    protected function getPackageProviders($app): array
    {
        $providers = [
            StatamicServiceProvider::class,
            InertiaServiceProvider::class,
            MailLogServiceProvider::class,
            ServiceProvider::class,
        ];

        if (class_exists(GraphQLServiceProvider::class)) {
            array_unshift($providers, GraphQLServiceProvider::class);
        }

        return $providers;
    }

    #[\Override]
    protected function getPackageAliases($app): array
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    /** @param Application $app */
    #[\Override]
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'bobwez98/mail-log-statamic' => [
                'id' => 'bobwez98/mail-log-statamic',
                'slug' => 'mail-log-statamic',
                'version' => 'dev-main',
                'namespace' => 'BobWez98\MailLogStatamic',
                'autoload' => 'src/',
                'provider' => ServiceProvider::class,
            ],
        ];

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
        $app['config']->set('inertia.testing.page_paths', [__DIR__.'/../resources/js/pages']);
        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.stache.stores.taxonomies.directory', __DIR__.'/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', __DIR__.'/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', __DIR__.'/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', __DIR__.'/__fixtures__/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', __DIR__.'/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.global-variables.directory', __DIR__.'/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', __DIR__.'/__fixtures__/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', __DIR__.'/__fixtures__/content/structures/collections');
        $app['config']->set('statamic.stache.stores.form-submissions.directory', __DIR__.'/__fixtures__/content/submissions');
        $app['config']->set('statamic.stache.stores.users.directory', __DIR__.'/__fixtures__/users');
    }

    protected function signInAsSuperUser(): void
    {
        $user = User::make();
        $this->assertInstanceOf(FileUser::class, $user);
        $user->id((string) Str::uuid());
        $user->email('super@example.com');
        $user->makeSuper();

        $this->actingAs($user);
    }

    protected function cpRoute(string $name, mixed $parameters = []): string
    {
        $route = cp_route($name, $parameters);

        if (! is_string($route)) {
            throw new UnexpectedValueException('The Control Panel route must resolve to a string.');
        }

        return $route;
    }

    /** @param array<string, mixed> $attributes */
    protected function createMailLog(array $attributes = []): MailLog
    {
        return MailLog::query()->create(array_merge([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::SUCCESS,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<html><body><h1>Quarterly report</h1></body></html>',
            'data' => ['campaign' => 'quarterly'],
            'sent_at' => now(),
        ], $attributes));
    }
}
