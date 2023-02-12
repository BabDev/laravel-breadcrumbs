<?php

namespace BabDev\Breadcrumbs\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * Exception that is thrown if the user attempts to generate breadcrumbs for a page that is not registered.
 */
class InvalidBreadcrumbException extends \InvalidArgumentException implements BreadcrumbsException, ProvidesSolution
{
    private bool $routeIsBounded = false;

    public function __construct(
        private readonly string $name,
    ) {
        parent::__construct(sprintf('Breadcrumb not found with name "%s"', $name));
    }

    public function isRouteBounded(): bool
    {
        return $this->routeIsBounded;
    }

    public function routeIsBounded(): void
    {
        $this->routeIsBounded = true;
    }

    public function getSolution(): Solution
    {
        // Determine the breadcrumbs file name
        $files = (array) config('breadcrumbs.files');

        if (\count($files) === 1) {
            $file = Str::replaceFirst(base_path() . \DIRECTORY_SEPARATOR, '', $files[0]);
        } else {
            $file = 'one of the files defined in config/breadcrumbs.php';
        }

        $route = request()->route();
        $routeName = $route instanceof Route ? $route->getName() : null;

        if ($routeName) {
            $url = sprintf("route('%s')", $this->name);
        } else {
            $url = sprintf("url('%s')", request()->path());
        }

        $links = [];
        $links['Defining breadcrumbs'] = 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x/defining-breadcrumbs';

        if ($this->routeIsBounded) {
            $links['Route-bound breadcrumbs'] = 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x/route-bound-breadcrumbs';
        }

        $links['Laravel Breadcrumbs documentation'] = 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x';

        $description = <<<DESC
            ```php
            \\Breadcrumbs::for('{$this->name}', function (\\BabDev\\Breadcrumbs\\Contracts\\BreadcrumbsGenerator \$trail) {
                \$trail->push('Title Here', $url);
            });
            ```
            DESC
        ;

        return BaseSolution::create(sprintf('Add this to %s', $file))
            ->setSolutionDescription($description)
            ->setDocumentationLinks($links);
    }
}
