<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;
use BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider;
use Breadcrumbs;
use Illuminate\Contracts\Foundation\Application;

class CustomChildServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CustomChildBreadcrumbsServiceProvider::class,
        ];
    }

    public function testRender()
    {
        $html = Breadcrumbs::render('home')->toHtml();

        $this->assertXmlStringEqualsXmlString('
            <ol>
                <li class="current">Home</li>
            </ol>
        ', $html);
    }
}

class CustomChildBreadcrumbsServiceProvider extends BreadcrumbsServiceProvider
{
    protected function registerBreadcrumbs(BreadcrumbsManager $breadcrumbs, Application $app): void
    {
        Breadcrumbs::for('home', function ($trail) {
            $trail->push('Home', '/');
        });
    }
}
