<?php

namespace BabDev\Breadcrumbs\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Exception that is thrown if the user attempts to render breadcrumbs for the current route but the current route doesn't have a name.
 */
class UnnamedRouteException extends \InvalidArgumentException implements BreadcrumbsException, ProvidesSolution
{
    public function __construct(
        private readonly Route $route,
    ) {
        $uri = Arr::first($route->methods()) . ' /' . ltrim($route->uri(), '/');

        parent::__construct(sprintf('The current route "%s" is not named', $uri));
    }

    public function getSolution(): Solution
    {
        $method = strtolower(Arr::first($this->route->methods()));
        $uri = $this->route->uri();
        $action = $this->route->getActionName();

        if ($action === '\Illuminate\Routing\ViewController') {
            $method = 'view';
            $action = sprintf("'%s'", $this->route->defaults['view'] ?? 'view-name');
        } elseif ($action === 'Closure') {
            $action = "function() {\n    ...\n}";
        } else {
            $action = sprintf("'%s'", Str::replaceFirst(app()->getNamespace() . 'Http\Controllers\\', '', $action));
        }

        $description = <<<DESC
            For example:

            ```php
            Route::$method('$uri', $action)->name('sample-name');
            ```
            DESC
        ;

        return BaseSolution::create('Give the route a name')
            ->setSolutionDescription($description)
            ->setDocumentationLinks(
                [
                    'Route-bound breadcrumbs' => 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x/route-bound-breadcrumbs',
                    'Laravel Breadcrumbs documentation' => 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x',
                ]
            );
    }
}
