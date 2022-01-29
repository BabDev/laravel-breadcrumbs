<?php

namespace BabDev\Breadcrumbs;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator as BreadcrumbsGeneratorContract;
use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;
use BabDev\Breadcrumbs\Events\BeforeBreadcrumbGenerated;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;

/**
 * Generate a set of breadcrumbs for a page.
 *
 * This is passed as the first parameter to all breadcrumb-generating closures. In the documentation it is named
 * `$breadcrumbs`.
 */
class BreadcrumbsGenerator implements BreadcrumbsGeneratorContract
{
    /**
     * Breadcrumbs currently being generated.
     */
    protected ?Collection $breadcrumbs = null;

    /**
     * @var array<string, callable>
     */
    protected array $callbacks = [];

    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    /**
     * @param array<string, callable> $callbacks The registered breadcrumb-generating callbacks.
     *
     * @return Collection<array-key, object>
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     */
    public function generate(array $callbacks, string $name, array $params): Collection
    {
        $this->breadcrumbs = new Collection();
        $this->callbacks = $callbacks;

        $event = new BeforeBreadcrumbGenerated($this, $name, $params);
        $this->dispatcher->dispatch($event);

        $name = $event->name;
        $params = $event->params;

        $this->call($name, $params);

        $this->dispatcher->dispatch(new AfterBreadcrumbGenerated($this, $name, $params));

        $breadcrumbs = $this->breadcrumbs;

        $this->breadcrumbs = null;

        return $breadcrumbs;
    }

    /**
     * @throws InvalidBreadcrumbException if the name is not registered
     */
    protected function call(string $name, array $params): void
    {
        if (!isset($this->callbacks[$name])) {
            throw new InvalidBreadcrumbException($name);
        }

        $this->callbacks[$name]($this, ...$params);
    }

    /**
     * Add breadcrumbs for a parent page.
     *
     * Should be called from the closure for a page, before `push()` is called.
     *
     * @param string $name      The name of the parent page.
     * @param array  ...$params The parameters to pass to the closure.
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     */
    public function parent(string $name, ...$params): void
    {
        $this->call($name, $params);
    }

    /**
     * Add a breadcrumb.
     *
     * Should be called from the closure for each page. May be called more than once.
     *
     * @param string      $title The title of the page.
     * @param string|null $url   The URL of the page.
     * @param array       $data  Optional associative array of additional data to pass to the view.
     *
     * @return void
     */
    public function push(string $title, string $url = null, array $data = []): void
    {
        $this->breadcrumbs->push((object) array_merge($data, compact('title', 'url')));
    }
}
