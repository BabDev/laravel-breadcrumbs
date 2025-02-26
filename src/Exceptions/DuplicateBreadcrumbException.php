<?php

namespace BabDev\Breadcrumbs\Exceptions;

/**
 * Exception that is thrown if the user attempts to register two breadcrumbs with the same name.
 */
class DuplicateBreadcrumbException extends \InvalidArgumentException implements BreadcrumbsException
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Breadcrumb name "%s" has already been registered', $name));
    }
}
