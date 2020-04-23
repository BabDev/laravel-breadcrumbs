<?php

namespace BabDev\Breadcrumbs\Tests\Exceptions;

use BabDev\Breadcrumbs\Exceptions\DuplicateBreadcrumbException;
use Orchestra\Testbench\TestCase;

class DuplicateBreadcrumbExceptionTest extends TestCase
{
    public function testProvidesSolutionWhenNoFilesAreConfigured()
    {
        $this->app['config']->set('breadcrumbs.files', []);

        $solution = (new DuplicateBreadcrumbException('duplicate'))->getSolution();

        $this->assertStringContainsString(
            'Check your application for multiple breadcrumbs named `duplicate`.',
            $solution->getSolutionDescription()
        );
    }

    public function testProvidesSolutionWhenASingleFileIsConfigured()
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__.'/../routes/breadcrumbs.php',
            ]
        );

        $solution = (new DuplicateBreadcrumbException('duplicate'))->getSolution();

        $this->assertStringMatchesFormat(
            'Look in `%s` for multiple breadcrumbs named `duplicate`.',
            $solution->getSolutionDescription()
        );
    }

    public function testProvidesSolutionWhenMultipleFilesAreConfigured()
    {
        $this->app['config']->set(
            'breadcrumbs.files',
            [
                __DIR__.'/../breadcrumbs/file1.php',
                __DIR__.'/../breadcrumbs/file2.php',
            ]
        );

        $solution = (new DuplicateBreadcrumbException('duplicate'))->getSolution();

        $this->assertStringMatchesFormat(
            'Look in the following files for multiple breadcrumbs named `duplicate`: %s',
            $solution->getSolutionDescription()
        );
    }
}
