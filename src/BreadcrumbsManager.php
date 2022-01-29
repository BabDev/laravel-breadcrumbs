<?php

namespace BabDev\Breadcrumbs;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager as BreadcrumbsManagerContract;
use BabDev\Breadcrumbs\Exceptions\DuplicateBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\UnnamedRouteException;
use BabDev\Breadcrumbs\Exceptions\ViewNotSetException;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

/**
 * The main Breadcrumbs singleton class, responsible for registering, generating and rendering breadcrumbs.
 */
class BreadcrumbsManager implements BreadcrumbsManagerContract
{
    use Macroable;

    /**
     * The registered breadcrumb-generating callbacks.
     *
     * @var array<string, callable>
     */
    protected array $callbacks = [];

    /**
     * The current route name and parameters.
     *
     * @var array<string, array>|null
     */
    protected $route;

    public function __construct(
        protected BreadcrumbsGenerator $generator,
        protected Router $router,
        protected ViewFactory $viewFactory,
    ) {
    }

    /**
     * @param string   $name     The name of the page.
     * @param callable $callback The callback, which should accept a {@link BreadcrumbsGenerator} instance as the first parameter and may accept additional parameters.
     *
     * @throws DuplicateBreadcrumbException if the given name has already been used.
     */
    public function for(string $name, callable $callback): void
    {
        if ($this->exists($name)) {
            throw new DuplicateBreadcrumbException($name);
        }

        $this->callbacks[$name] = $callback;
    }

    /**
     * @param string|null $name The page name, defaults to the current route name.
     */
    public function exists(?string $name = null): bool
    {
        if (null === $name) {
            try {
                [$name] = $this->getCurrentRoute();
            } catch (UnnamedRouteException) {
                return false;
            }
        }

        return isset($this->callbacks[$name]);
    }

    /**
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @return Collection<array-key, object>
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     */
    public function generate(?string $name = null, ...$params): Collection
    {
        $origName = $name;

        // Route-bound breadcrumbs
        if ($name === null) {
            try {
                [$name, $params] = $this->getCurrentRoute();
            } catch (UnnamedRouteException $e) {
                if (config('breadcrumbs.unnamed-route-exception')) {
                    throw $e;
                }

                return new Collection();
            }
        }

        // Generate breadcrumbs
        try {
            return $this->generator->generate($this->callbacks, $name, $params);
        } catch (InvalidBreadcrumbException $e) {
            if ($origName === null && config('breadcrumbs.missing-route-bound-breadcrumb-exception')) {
                $e->routeIsBounded();

                throw $e;
            }

            if ($origName !== null && config('breadcrumbs.invalid-named-breadcrumb-exception')) {
                throw $e;
            }

            return new Collection();
        }
    }

    /**
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     * @throws ViewNotSetException        if no view has been set
     */
    public function view(string $view, ?string $name = null, ...$params): View
    {
        return $this->viewFactory->make(
            $view,
            [
                'breadcrumbs' => $this->generate($name, ...$params),
            ]
        );
    }

    /**
     * @param string|null $name      The page name, defaults to the current route name.
     * @param mixed       ...$params The parameters to pass to the closure for the current page.
     *
     * @return View
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if no name is given and the current route doesn't have an associated name
     * @throws ViewNotSetException        if no view has been set
     */
    public function render(?string $name = null, ...$params): View
    {
        $view = config('breadcrumbs.view');

        if (!$view) {
            throw new ViewNotSetException('Breadcrumbs view not specified (check config/breadcrumbs.php)');
        }

        return $this->view($view, $name, ...$params);
    }

    /**
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     * @throws UnnamedRouteException      if the current route doesn't have an associated name
     */
    public function current(): ?object
    {
        return $this->generate()->where('current', '!==', false)->last();
    }

    /**
     * Get the current route name and parameters.
     *
     * This may be the route set manually with the setCurrentRoute() method, but normally is the route retrieved from
     * the Laravel Router.
     *
     * #### Example
     * ```php
     * [$name, $params] = $this->getCurrentRoute();
     * ```
     *
     * @return array<string, array> A two-element array consisting of the route name (string) and any parameters (array).
     *
     * @throws UnnamedRouteException if the current route doesn't have an associated name
     */
    protected function getCurrentRoute(): array
    {
        // Manually set route
        if ($this->route) {
            return $this->route;
        }

        // Determine the current route
        $route = $this->router->current();

        // No current route - must be the 404 page
        if ($route === null) {
            return ['errors.404', []];
        }

        // Convert route to name
        $name = $route->getName();

        if ($name === null) {
            throw new UnnamedRouteException($route);
        }

        // Get the current route parameters
        $params = array_values($route->parameters());

        return [$name, $params];
    }

    /**
     * Set the current route name and parameters to use when calling render() or generate() with no parameters.
     *
     * @param string $name      The name of the current page.
     * @param mixed  ...$params The parameters to pass to the closure for the current page.
     */
    public function setCurrentRoute(string $name, ...$params): void
    {
        $this->route = [$name, $params];
    }

    /**
     * Clear the previously set route name and parameters to use when calling render() or generate() with no parameters.
     *
     * Next time it will revert to the default behavior of using the current route from Laravel.
     */
    public function clearCurrentRoute(): void
    {
        $this->route = null;
    }
}
