<?php

namespace BabDev\Breadcrumbs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BabDev\Breadcrumbs\BreadcrumbsManager
 */
class Breadcrumbs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'breadcrumbs.manager';
    }
}
