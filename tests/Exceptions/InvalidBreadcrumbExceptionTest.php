<?php

namespace BabDev\Breadcrumbs\Tests\Exceptions;

use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase;

class InvalidBreadcrumbExceptionTest extends TestCase
{
    public function testProvidesSolutionWhenASingleFileIsConfigured(): void
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__ . '/../routes/breadcrumbs.php',
            ]
        );

        $solution = (new InvalidBreadcrumbException('invalid'))->getSolution();

        $description = <<<DESC
```php
Breadcrumbs::for('invalid', function (\$trail) {
    \$trail->push('Title Here', url('%s'));
});
```
DESC;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );

        $this->assertArrayNotHasKey('Route-bound breadcrumbs', $solution->getDocumentationLinks());
    }

    public function testProvidesSolutionWhenMultipleFilesAreConfigured(): void
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__ . '/../breadcrumbs/file1.php',
                __DIR__ . '/../breadcrumbs/file2.php',
            ]
        );

        $solution = (new InvalidBreadcrumbException('invalid'))->getSolution();

        $description = <<<DESC
```php
Breadcrumbs::for('invalid', function (\$trail) {
    \$trail->push('Title Here', url('%s'));
});
```
DESC;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );

        $this->assertArrayNotHasKey('Route-bound breadcrumbs', $solution->getDocumentationLinks());
    }

    public function testProvidesSolutionWithAppropriateDocumentationLinkWhenRouteIsBounded(): void
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__ . '/../routes/breadcrumbs.php',
            ]
        );

        $exception = new InvalidBreadcrumbException('invalid');
        $exception->routeIsBounded();

        $solution = $exception->getSolution();

        $description = <<<DESC
```php
Breadcrumbs::for('invalid', function (\$trail) {
    \$trail->push('Title Here', url('%s'));
});
```
DESC;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );

        $this->assertArrayHasKey('Route-bound breadcrumbs', $solution->getDocumentationLinks());
    }

    public function testProvidesSolutionWhenThereIsARouteInTheRequest(): void
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__ . '/../routes/breadcrumbs.php',
            ]
        );

        $route = $this->app['router']->get('/', static function (): Response {
            return new Response();
        })->name('home');

        $this->app['request']->setRouteResolver(static function () use ($route): Route {
            return $route;
        });

        $exception = new InvalidBreadcrumbException('invalid');
        $exception->routeIsBounded();

        $solution = $exception->getSolution();

        $description = <<<DESC
```php
Breadcrumbs::for('invalid', function (\$trail) {
    \$trail->push('Title Here', route('%s'));
});
```
DESC;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );

        $this->assertArrayHasKey('Route-bound breadcrumbs', $solution->getDocumentationLinks());
    }
}
