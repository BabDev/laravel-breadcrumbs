<?php

namespace BabDev\Breadcrumbs;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;

/**
 * Class used to load breadcrumb files in a scope isolated manner
 *
 * @internal
 */
final class BreadcrumbFileRegistrar
{
    public function __construct(
        private readonly BreadcrumbsManager $breadcrumbs,
    ) {
    }

    public function register(string $file): void
    {
        self::registerFile($this->breadcrumbs, $file);
    }

    private static function registerFile(BreadcrumbsManager $breadcrumbs, string $file): void
    {
        require $file;
    }
}
