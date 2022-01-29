<?php

namespace BabDev\Breadcrumbs\Contracts;

use BabDev\Breadcrumbs\Exceptions\DuplicateBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\UnnamedRouteException;
use BabDev\Breadcrumbs\Exceptions\ViewNotSetException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

interface BreadcrumbsManager
{
    /**
     * Registers a breadcrumb-generating callback.
     *
     * @param callable $callback The callback, which should accept a {@link BreadcrumbsGenerator} instance as the first parameter and may accept additional parameters.
     *
     * @throws DuplicateBreadcrumbException if the given name has already been used.
     */
    public function for(string $name, callable $callback): void;

    /**
     * Check if a breadcrumb with the given name exists.
     *
     * @param string|null $name The page name, defaults to the current route name.
     */
    public function exists(?string $name = null): bool;

    /**
     * Generate a set of breadcrumbs for a page.
     *
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @return Collection<array-key, object>
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     */
    public function generate(?string $name = null, ...$params): Collection;

    /**
     * Render breadcrumbs for a page with the specified view.
     *
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     * @throws ViewNotSetException        if no view has been set
     */
    public function view(string $view, ?string $name = null, ...$params): View;

    /**
     * Render breadcrumbs for a page with the default view.
     *
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     * @throws ViewNotSetException        if no view has been set
     */
    public function render(?string $name = null, ...$params): View;

    /**
     * Get the last breadcrumb for the current page.
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if the current route doesn't have an associated name
     */
    public function current(): ?object;
}
