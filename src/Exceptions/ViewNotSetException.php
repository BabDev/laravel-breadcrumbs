<?php

namespace BabDev\Breadcrumbs\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

/**
 * Exception that is thrown if the user attempts to render breadcrumbs without setting a view.
 */
class ViewNotSetException extends \RuntimeException implements BreadcrumbsException, ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('Set a view for Laravel Breadcrumbs')
            ->setSolutionDescription('Please check `config/breadcrumbs.php` for a valid view (e.g. "breadcrumbs::bootstrap4")')
            ->setDocumentationLinks(
                [
                    'Choosing a breadcrumbs template (view)' => 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x/installation',
                    'Laravel Breadcrumbs documentation' => 'https://www.babdev.com/open-source/packages/laravel-breadcrumbs/docs/2.x',
                ]
            );
    }
}
