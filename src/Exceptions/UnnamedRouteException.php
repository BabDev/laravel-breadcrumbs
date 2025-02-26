<?php

namespace BabDev\Breadcrumbs\Exceptions;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

/**
 * Exception that is thrown if the user attempts to render breadcrumbs for the current route but the current route doesn't have a name.
 */
class UnnamedRouteException extends \InvalidArgumentException implements BreadcrumbsException
{
    public function __construct(Route $route)
    {
        $uri = Arr::first($route->methods()) . ' /' . ltrim($route->uri(), '/');

        parent::__construct(\sprintf('The current route "%s" is not named', $uri));
    }
}
