<?php

namespace BabDev\Breadcrumbs\Tests\Exceptions;

use BabDev\Breadcrumbs\Exceptions\UnnamedRouteException;
use Illuminate\Http\Response;
use Orchestra\Testbench\TestCase;

class UnnamedRouteExceptionTest extends TestCase
{
    public function testProvidesSolutionForAClosureRoute()
    {
        $route = $this->app['router']->get('/blog', static function (): Response {
            return new Response();
        });

        $solution = (new UnnamedRouteException($route))->getSolution();

        $description = <<<DESC
For example:

```php
Route::get('blog', function() {
    ...
})->name('sample-name');
```
DESC
        ;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );
    }

    public function testProvidesSolutionForAnUnnamedRouteWithAControllerAction()
    {
        $route = $this->app['router']->get('/blog', 'App\Http\Controllers\BlogController@index');

        $solution = (new UnnamedRouteException($route))->getSolution();

        $description = <<<DESC
For example:

```php
Route::get('blog', 'BlogController@index')->name('sample-name');
```
DESC
        ;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );
    }

    public function testProvidesSolutionForAnUnnamedViewRoute()
    {
        $route = $this->app['router']->view('/blog', 'blog');

        $solution = (new UnnamedRouteException($route))->getSolution();

        $description = <<<DESC
For example:

```php
Route::view('blog', 'blog')->name('sample-name');
```
DESC
        ;

        $this->assertStringMatchesFormat(
            $description,
            $solution->getSolutionDescription()
        );
    }
}
