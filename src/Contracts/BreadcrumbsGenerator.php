<?php

namespace BabDev\Breadcrumbs\Contracts;

use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Illuminate\Support\Collection;

interface BreadcrumbsGenerator
{
    /**
     * Generate breadcrumbs.
     *
     * @param array<string, callable> $callbacks The registered breadcrumb-generating callbacks.
     * @param string                  $name      The name of the current page.
     * @param array                   $params    The parameters to pass to the closure for the current page.
     *
     * @return Collection<array-key, object>
     *
     * @throws InvalidBreadcrumbException if the name is (or any ancestor names are) not registered
     */
    public function generate(array $callbacks, string $name, array $params): Collection;

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
    public function parent(string $name, ...$params): void;

    /**
     * Add a breadcrumb.
     *
     * Should be called from the closure for each page. May be called more than once.
     *
     * @param string      $title The title of the page.
     * @param string|null $url   The URL of the page.
     * @param array       $data  Optional associative array of additional data to pass to the view.
     */
    public function push(string $title, string $url = null, array $data = []): void;
}
