<?php
/*
 * Breadcrumbs file which utilizes the facade
 */

use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;

Breadcrumbs::for('multiple-file-test-parent', static function (BreadcrumbsGenerator $trail): void {
    $trail->push('Parent');
});
