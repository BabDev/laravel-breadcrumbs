<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\BreadcrumbsManager;
use BabDev\Breadcrumbs\Exceptions\DuplicateBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\UnnamedRouteException;
use BabDev\Breadcrumbs\Exceptions\ViewNotSetException;
use BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Spatie\Snapshots\MatchesSnapshots;

class BreadcrumbsManagerTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @var BreadcrumbsGenerator|MockObject
     */
    private $generator;

    /**
     * @var Router|MockObject
     */
    private $router;

    /**
     * @var ViewFactory|MockObject
     */
    private $viewFactory;

    /**
     * @var BreadcrumbsManager
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->createMock(BreadcrumbsGenerator::class);
        $this->router = $this->createMock(Router::class);
        $this->viewFactory = $this->createMock(ViewFactory::class);

        $this->manager = new BreadcrumbsManager($this->generator, $this->router, $this->viewFactory);
    }

    protected function getPackageProviders($app)
    {
        return [
            BreadcrumbsServiceProvider::class,
        ];
    }

    private function getManager(): BreadcrumbsManager
    {
        /** @var BreadcrumbsManager $manager */
        $manager = $this->app->make('breadcrumbs.manager');

        return $manager;
    }

    public function testACallbackIsRegistered(): void
    {
        $this->manager->for('test', static function (): void {});

        $this->assertTrue($this->manager->exists('test'));
    }

    public function testACallbackIsNotRegisteredWhenItHasADuplicateName(): void
    {
        $this->expectException(DuplicateBreadcrumbException::class);

        $this->manager->for('test', static function (): void {});
        $this->manager->for('test', static function (): void {});
    }

    public function testANamedCallbackExists(): void
    {
        $this->manager->for('test', static function (): void {});

        $this->assertTrue($this->manager->exists('test'));
        $this->assertFalse($this->manager->exists('not-present'));
    }

    public function testANullCallbackNameChecksIfACallbackExistsForTheRouteName(): void
    {
        $route = new Route(['GET'], '/test', ['as' => 'test']);
        $route->parameters = [];

        $this->router->expects($this->once())
            ->method('current')
            ->willReturn($route);

        $this->manager->for('test', static function (): void {});

        $this->assertTrue($this->manager->exists(null));
    }

    public function testANullCallbackNameChecksIfACallbackExistsForThe404Route(): void
    {
        $this->router->expects($this->once())
            ->method('current')
            ->willReturn(null);

        $this->manager->for('errors.404', static function (): void {});

        $this->assertTrue($this->manager->exists(null));
    }

    public function testANullCallbackNameChecksIfACallbackExistsForAManuallyRegisteredRoute(): void
    {
        $this->router->expects($this->never())
            ->method('current');

        $this->manager->for('test', static function (): void {});
        $this->manager->setCurrentRoute('test', []);

        $this->assertTrue($this->manager->exists(null));
    }

    public function testAListOfBreadcrumbsCanBeGenerated(): void
    {
        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn(new Collection());

        $this->manager->for('test', static function (): void {});

        $this->assertInstanceOf(Collection::class, $this->manager->generate('test'));
    }

    public function testAListOfBreadcrumbsCanBeGeneratedFromANamedRoute(): void
    {
        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn(new Collection());

        $this->manager->for('test', static function (): void {});
        $this->manager->setCurrentRoute('test', []);

        $this->assertInstanceOf(Collection::class, $this->manager->generate(null));
    }

    public function testAnEmptyBreadcrumbListIsReturnedIfAnUnnamedRouteExceptionIsThrownWhenGuessingTheName(): void
    {
        $this->app['config']->set('breadcrumbs.unnamed-route-exception', false);

        $route = new Route(['GET'], '/test', []);
        $route->parameters = [];

        $this->router->expects($this->once())
            ->method('current')
            ->willReturn($route);

        $this->manager->for('test', static function (): void {});

        $breadcrumbs = $this->manager->generate(null);

        $this->assertInstanceOf(Collection::class, $breadcrumbs);
        $this->assertEmpty($breadcrumbs);
    }

    public function testAnEmptyBreadcrumbListIsReturnedIfAnInvalidBreadcrumbExceptionIsThrownWhenGeneratingTheListIfNoBreadcrumbNameWasProvided(): void
    {
        $this->app['config']->set('breadcrumbs.missing-route-bound-breadcrumb-exception', false);
        $this->app['config']->set('breadcrumbs.invalid-named-breadcrumb-exception', false);

        $route = new Route(['GET'], '/test', ['as' => 'test']);
        $route->parameters = [];

        $this->router->expects($this->once())
            ->method('current')
            ->willReturn($route);

        $this->generator->expects($this->once())
            ->method('generate')
            ->willThrowException(new InvalidBreadcrumbException('test'));

        $breadcrumbs = $this->manager->generate(null);

        $this->assertInstanceOf(Collection::class, $breadcrumbs);
        $this->assertEmpty($breadcrumbs);
    }

    public function testAnExceptionIsThrownWhenGeneratingBreadcrumbsIfAnInvalidBreadcrumbExceptionIsThrownWhenGeneratingTheListIfNoBreadcrumbNameWasProvidedForABoundedRoute(): void
    {
        $this->app['config']->set('breadcrumbs.missing-route-bound-breadcrumb-exception', true);
        $this->app['config']->set('breadcrumbs.invalid-named-breadcrumb-exception', false);

        $route = new Route(['GET'], '/test', ['as' => 'test']);
        $route->parameters = [];

        $this->router->expects($this->once())
            ->method('current')
            ->willReturn($route);

        $this->generator->expects($this->once())
            ->method('generate')
            ->willThrowException(new InvalidBreadcrumbException('test'));

        try {
            $this->manager->generate(null);

            $this->fail(sprintf('A %s should have been thrown.', InvalidBreadcrumbException::class));
        } catch (InvalidBreadcrumbException $exception) {
            $this->assertTrue($exception->isRouteBounded());
        }
    }

    public function testAnExceptionIsThrownWhenGeneratingBreadcrumbsIfAnInvalidBreadcrumbExceptionIsThrownWhenGeneratingTheList(): void
    {
        $this->app['config']->set('breadcrumbs.missing-route-bound-breadcrumb-exception', false);
        $this->app['config']->set('breadcrumbs.invalid-named-breadcrumb-exception', true);

        $this->router->expects($this->never())
            ->method('current');

        $this->generator->expects($this->once())
            ->method('generate')
            ->willThrowException(new InvalidBreadcrumbException('test'));

        try {
            $this->manager->generate('test');

            $this->fail(sprintf('A %s should have been thrown.', InvalidBreadcrumbException::class));
        } catch (InvalidBreadcrumbException $exception) {
            $this->assertFalse($exception->isRouteBounded());
        }
    }

    public function testAnExceptionIsThrownWhenGeneratingBreadcrumbsIfAnUnnamedRouteWasRequested(): void
    {
        $this->expectException(UnnamedRouteException::class);

        $this->app['config']->set('breadcrumbs.unnamed-route-exception', true);

        $route = new Route(['GET'], '/test', []);
        $route->parameters = [];

        $this->router->expects($this->once())
            ->method('current')
            ->willReturn($route);

        $this->manager->for('test', static function (): void {});

        $this->manager->generate(null);
    }

    public function testAViewCanBeCreatedFromTheBreadcrumbList(): void
    {
        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn(new Collection());

        $this->viewFactory->expects($this->once())
            ->method('make')
            ->willReturn($this->createMock(View::class));

        $this->manager->for('test', static function (): void {});

        $this->assertInstanceOf(View::class, $this->manager->view('breadcrumbs', 'test'));
    }

    public function testTheBreadcrumbsCanBeRenderedFromTheDefaultView(): void
    {
        $this->app['config']->set('breadcrumbs.view', 'breadcrumbs');

        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn(new Collection());

        $this->viewFactory->expects($this->once())
            ->method('make')
            ->willReturn($this->createMock(View::class));

        $this->manager->for('test', static function (): void {});

        $this->assertInstanceOf(View::class, $this->manager->render('test'));
    }

    public function testTheBreadcrumbsAreNotRenderedIfThereIsNoDefaultView(): void
    {
        $this->expectException(ViewNotSetException::class);

        $this->app['config']->set('breadcrumbs.view', null);

        $this->generator->expects($this->never())
            ->method('generate');

        $this->viewFactory->expects($this->never())
            ->method('make');

        $this->manager->render('test');
    }

    public function testTheCurrentBreadcrumbCanBeRetrieved(): void
    {
        $current = (object) [
            'title' => 'Test',
            'url' => '/test',
            'current' => true,
        ];

        $breadcrumbs = new Collection();
        $breadcrumbs->add($current);

        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturn($breadcrumbs);

        $this->manager->for('test', static function (): void {});

        $this->assertSame($current, $this->manager->current());
    }

    public function packageViews(): \Generator
    {
        foreach (\glob(__DIR__ . '/../resources/views/*.blade.php') as $filename) {
            $name = \basename($filename, '.blade.php');

            yield $name => [$name];
        }
    }

    /**
     * @dataProvider packageViews
     */
    public function testPackageViewsAreRendered(string $view): void
    {
        $manager = $this->getManager();

        // Home (Normal link)
        $manager->for('home', static function (BreadcrumbsGenerator $trail): void {
            $trail->push('Home', url('/'));
        });

        // Home > Blog (Not a link)
        $manager->for('blog', static function (BreadcrumbsGenerator $trail): void {
            $trail->parent('home');
            $trail->push('Blog');
        });

        // Home > Blog > [Category] (Active page)
        $manager->for('category', static function (BreadcrumbsGenerator $trail, $category): void {
            $trail->parent('blog');
            $trail->push($category->title, url(sprintf('blog/category/%s', $category->id)));
        });

        $this->assertMatchesXmlSnapshot(
            $manager->view(
                sprintf('breadcrumbs::%s', $view),
                'category',
                (object) [
                    'id' => 456,
                    'title' => 'Sample Category',
                ]
            )->render()
        );
    }
}
