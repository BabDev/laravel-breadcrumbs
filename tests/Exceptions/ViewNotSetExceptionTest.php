<?php

namespace BabDev\Breadcrumbs\Tests\Exceptions;

use BabDev\Breadcrumbs\Exceptions\ViewNotSetException;
use Orchestra\Testbench\TestCase;

class ViewNotSetExceptionTest extends TestCase
{
    public function testProvidesSolution(): void
    {
        $solution = (new ViewNotSetException('Breadcrumbs view not specified (check config/breadcrumbs.php)'))->getSolution();

        $this->assertSame(
            'Please check `config/breadcrumbs.php` for a valid view (e.g. "breadcrumbs::bootstrap4")',
            $solution->getSolutionDescription()
        );
    }
}
