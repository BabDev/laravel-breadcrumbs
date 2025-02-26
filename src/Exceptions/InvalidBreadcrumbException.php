<?php

namespace BabDev\Breadcrumbs\Exceptions;

/**
 * Exception that is thrown if the user attempts to generate breadcrumbs for a page that is not registered.
 */
class InvalidBreadcrumbException extends \InvalidArgumentException implements BreadcrumbsException
{
    private bool $routeIsBounded = false;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Breadcrumb not found with name "%s"', $name));
    }

    public function isRouteBounded(): bool
    {
        return $this->routeIsBounded;
    }

    public function routeIsBounded(): void
    {
        $this->routeIsBounded = true;
    }
}
