<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use BabDev\Breadcrumbs\Facades\Breadcrumbs;
use BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider;
use BabDev\Breadcrumbs\Tests\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Orchestra\Testbench\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class AdvancedUsageTest extends TestCase
{
    use MatchesSnapshots;

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

    public function testBeforeAndAfterCallbacks()
    {
        \Breadcrumbs::before(static function (BreadcrumbsGenerator $trail): void {
            $trail->push('Before');
        });

        \Breadcrumbs::for('home', static function (BreadcrumbsGenerator $trail): void {
            $trail->push('Home', route('home'));
        });

        \Breadcrumbs::after(static function (BreadcrumbsGenerator $trail): void {
            $page = (int) request('page', 1);

            if ($page > 1) {
                $trail->push("Page $page");
            }
        });

        \Route::name('home')
            ->get('/', static function (): View {
                return \Breadcrumbs::render('home');
            });

        $this->assertMatchesHtmlSnapshot($this->get('/?page=2')->content());
    }

    public function testCurrentPageBreadcrumb()
    {
        \Route::name('home')
            ->get('/', static function () {
            });

        \Route::name('post')
            ->middleware(SubstituteBindings::class)
            ->get('/post/{post}', static function (BreadcrumbsManager $manager, Post $post): string {
                return $manager->current()->title;
            });

        \Breadcrumbs::for('post', static function (BreadcrumbsGenerator $trail, Post $post): void {
            $trail->push('Home', route('home'));
            $trail->push($post->title, route('post', $post));
            $trail->push('Page 2', null, ['current' => false]);
        });

        $html = $this->get('/post/1')->content();

        $this->assertSame('Post 1', $html);
    }

    public function testSetCurrentRoute()
    {
        \Breadcrumbs::for('sample', static function (BreadcrumbsGenerator $trail): void {
            $trail->push('Sample');
        });

        \Breadcrumbs::setCurrentRoute('sample');

        $this->assertMatchesHtmlSnapshot(\Breadcrumbs::render()->render());
    }

    public function testSetCurrentRouteWithParams()
    {
        \Breadcrumbs::for('sample', static function (BreadcrumbsGenerator $trail, int $a, int $b): void {
            $trail->push("Sample $a, $b");
        });

        \Breadcrumbs::setCurrentRoute('sample', 1, 2);

        $this->assertMatchesHtmlSnapshot(\Breadcrumbs::render()->toHtml());
    }

    public function testClearCurrentRoute()
    {
        $this->expectException(InvalidBreadcrumbException::class);

        \Breadcrumbs::for('sample', static function (BreadcrumbsGenerator $trail, int $a, int $b): void {
            $trail->push("Sample $a, $b");
        });

        \Breadcrumbs::setCurrentRoute('sample', 1, 2);
        \Breadcrumbs::clearCurrentRoute();

        \Breadcrumbs::render();
    }
}
