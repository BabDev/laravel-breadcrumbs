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
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $routeIsBounded = false;

    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Breadcrumb not found with name "%s"', $name));

        $this->name = $name;
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
            $url = \sprintf("route('%s')", $this->name);
        } else {
            $url = \sprintf("url('%s')", request()->path());
        }

        $links = [];
        $links['Defining breadcrumbs'] = 'https://github.com/BabDev/laravel-breadcrumbs#defining-breadcrumbs';

        if ($this->routeIsBounded) {
            $links['Route-bound breadcrumbs'] = 'https://github.com/BabDev/laravel-breadcrumbs#route-bound-breadcrumbs';
        }

        $links['Silencing breadcrumb exceptions'] = 'https://github.com/BabDev/laravel-breadcrumbs#configuration-file';
        $links['Laravel Breadcrumbs documentation'] = 'https://github.com/BabDev/laravel-breadcrumbs#laravel-breadcrumbs';

        $description = <<<DESC
```php
Breadcrumbs::for('{$this->name}', function (\$trail) {
    \$trail->push('Title Here', $url);
});
```
DESC
        ;

        return BaseSolution::create(\sprintf('Add this to %s', $file))
            ->setSolutionDescription($description)
            ->setDocumentationLinks($links);
    }
}
