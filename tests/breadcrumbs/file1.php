<?php
/*
 * Breadcrumbs file which utilizes injected variable from registrar
 */

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;

/** @var $breadcrumbs BreadcrumbsManager */

$breadcrumbs->for('multiple-file-test', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('multiple-file-test-parent');
    $trail->push('Loaded');
});
