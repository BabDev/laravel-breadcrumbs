<?php

namespace BabDev\Breadcrumbs\Facades;

use BabDev\Breadcrumbs\BreadcrumbsManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Traits\Macroable;

/**
 * @method static void        for(string $name, callable $callback)
 * @method static bool        exists(?string $name = null)
 * @method static Collection  generate(?string $name = null, ...$params)
 * @method static View        view(string $view, ?string $name = null, ...$params)
 * @method static View        render(?string $name = null, ...$params)
 * @method static object|null current()
 *
 * @mixin Macroable
 * @see BreadcrumbsManager
 */
class Breadcrumbs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'breadcrumbs.manager';
    }
}
