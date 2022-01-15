<?php

Breadcrumbs::for('single-file-test', function ($trail): void {
    $trail->push('Loaded');
});
