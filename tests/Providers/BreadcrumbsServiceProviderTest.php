<?php

namespace BabDev\Breadcrumbs\Tests\Providers;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;
use BabDev\Breadcrumbs\Facades\Breadcrumbs;
use BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Orchestra\Testbench\TestCase;

class BreadcrumbsServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            BreadcrumbsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Breadcrumbs' => Breadcrumbs::class,
        ];
    }

    public function testProviderIsDeferred(): void
    {
        $this->assertTrue($this->app->getProvider(BreadcrumbsServiceProvider::class)->isDeferred());
    }

    public function testDeferredServicesAreListed(): void
    {
        $this->assertTrue(
            \in_array(
                'breadcrumbs.generator',
                $this->app->getProvider(BreadcrumbsServiceProvider::class)->provides()
            )
        );
    }

    public function testServicesAreBound(): void
    {
        $this->assertTrue($this->app->bound('breadcrumbs.generator'));
        $this->assertInstanceOf(BreadcrumbsGenerator::class, $this->app->make('breadcrumbs.generator'));

        $this->assertTrue($this->app->bound('breadcrumbs.manager'));
        $this->assertInstanceOf(BreadcrumbsManager::class, $this->app->make('breadcrumbs.manager'));
    }

    public function testTheManagerIsResolvedWhenThereAreNoFiles(): void
    {
        $this->app['config']->set('breadcrumbs.files', []);

        $this->assertInstanceOf(BreadcrumbsManager::class, $this->app->make('breadcrumbs.manager'));
    }

    public function testTheManagerIsResolvedWhenThereAreFiles(): void
    {
        $this->app['config']->set('breadcrumbs.files', \glob(__DIR__ . '/../breadcrumbs/*.php'));

        $this->assertInstanceOf(BreadcrumbsManager::class, $this->app->make('breadcrumbs.manager'));
    }

    public function testTheManagerIsNotResolvedWhenAConfiguredFileIsMissing(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->app['config']->set('breadcrumbs.files', [__DIR__ . '/non-existing.php']);

        $this->app->make('breadcrumbs.manager');
    }
}
