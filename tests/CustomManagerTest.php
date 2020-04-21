<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\BreadcrumbsManager;
use Breadcrumbs;
use Config;
use Illuminate\Support\Collection;

class CustomManagerTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Need to inject this early, before the package is loaded, to simulate it being set in the config file
        $app['config']['breadcrumbs.manager-class'] = CustomManager::class;
    }

    public function testCustomManager()
    {
        $breadcrumbs = Breadcrumbs::generate();

        $this->assertSame('custom-manager', $breadcrumbs[0]);
    }
}

class CustomManager extends BreadcrumbsManager
{
    public function generate(string $name = null, ...$params): Collection
    {
        return new Collection(['custom-manager']);
    }
}
