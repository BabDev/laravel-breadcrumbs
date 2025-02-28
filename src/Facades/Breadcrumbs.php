<?php

namespace BabDev\Breadcrumbs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void for(string $name, callable $callback)
 * @method static bool exists(string|null $name = null)
 * @method static \Illuminate\Support\Collection generate(string|null $name = null, mixed ...$params)
 * @method static \Illuminate\Contracts\View\View view(string $view, string|null $name = null, mixed ...$params)
 * @method static \Illuminate\Contracts\View\View render(string|null $name = null, mixed ...$params)
 * @method static object|null current()
 * @method static void setCurrentRoute(string $name, mixed ...$params)
 * @method static void clearCurrentRoute()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \BabDev\Breadcrumbs\BreadcrumbsManager
 */
class Breadcrumbs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'breadcrumbs.manager';
    }
}
