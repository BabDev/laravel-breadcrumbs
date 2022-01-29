<?php

namespace BabDev\Breadcrumbs\Events;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;

abstract class BreadcrumbGenerated
{
    public function __construct(
        public BreadcrumbsGenerator $breadcrumbs,
        public string $name,
        public array $params
    ) {
    }
}
