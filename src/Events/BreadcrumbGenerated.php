<?php

namespace BabDev\Breadcrumbs\Events;

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;

abstract class BreadcrumbGenerated
{
    /**
     * @var BreadcrumbsGenerator
     */
    public $breadcrumbs;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $params;

    public function __construct(BreadcrumbsGenerator $breadcrumbs, string $name, array $params)
    {
        $this->breadcrumbs = $breadcrumbs;
        $this->name = $name;
        $this->params = $params;
    }
}
