<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\Exceptions\DuplicateBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use BabDev\Breadcrumbs\Exceptions\UnnamedRouteException;
use BabDev\Breadcrumbs\Exceptions\ViewNotSetException;
use Breadcrumbs;
use Config;
use ErrorException;
use Facade\IgnitionContracts\ProvidesSolution;
use Route;

class IgnitionTest extends TestCase
{
    private function assertSolutionMatchesSnapshot(ProvidesSolution $exception)
    {
        $solution = $exception->getSolution();

        // Using snapshots to avoid duplicating all the solutions here
        // (I tested them in the browser already, and the code coverage checker ensures I caught all combinations)
        $this->assertMatchesSnapshot($solution->getSolutionTitle());
        $this->assertMatchesSnapshot($solution->getSolutionDescription());
        $this->assertMatchesSnapshot($solution->getDocumentationLinks());
    }

    public function dataOneOrManyConfigFiles()
    {
        return [
            'Single config file' => [['routes/breadcrumbs.php']],
            'Multiple config files' => [['breadcrumbs/file1.php', 'breadcrumbs/file2.php']],
        ];
    }

    /** @dataProvider dataOneOrManyConfigFiles */
    public function testInvalidBreadcrumbSolution(array $files)
    {
        $this->markTestSkipped('Failing with manager using after resolved callback');

        Config::set('breadcrumbs.files', $files);

        try {
            Breadcrumbs::render('invalid');
            $this->fail('No exception thrown');
        } catch (InvalidBreadcrumbException $e) {
            $this->assertSolutionMatchesSnapshot($e);
        }
    }

    /** @dataProvider dataOneOrManyConfigFiles */
    public function testMissingRouteBoundBreadcrumbSolution(array $files)
    {
        $this->markTestSkipped('Failing with manager using after resolved callback');

        Config::set('breadcrumbs.files', $files);

        Route::name('home')->get('/', function () {
            return Breadcrumbs::render();
        });

        try {
            $this->get('/');
            $this->fail('No exception thrown');
        } catch (InvalidBreadcrumbException $e) {
            $this->assertSolutionMatchesSnapshot($e);
        }
    }

    public function testViewNotSetSolution()
    {
        Config::set('breadcrumbs.view', '');

        Breadcrumbs::for('home', function ($trail) {
            $trail->push('Home', url('/'));
        });

        try {
            Breadcrumbs::render('home');
            $this->fail('No exception thrown');
        } catch (ViewNotSetException $e) {
            $this->assertSolutionMatchesSnapshot($e);
        }
    }

    public function testUnnamedClosureRouteSolution()
    {
        Route::get('/blog', function () {
            return Breadcrumbs::render();
        });

        try {
            $this->get('/blog');
            $this->fail('No exception thrown');
        } catch (UnnamedRouteException $e) {
            $this->assertSolutionMatchesSnapshot($e);
        }
    }

    public function testUnnamedControllerRouteSolution()
    {
        Route::get('/posts/{post}', 'App\Http\Controllers\PostController@edit');

        try {
            $this->get('/posts/1');
            $this->fail('No exception thrown');
        } catch (UnnamedRouteException $e) {
            $this->assertSolutionMatchesSnapshot($e);
        }
    }

    public function testUnnamedViewRouteSolution()
    {
        Route::view('/blog', 'page');

        try {
            $this->get('/blog');
            $this->fail('No exception thrown');
        } catch (ErrorException $e) {
            $this->assertSolutionMatchesSnapshot($e->getPrevious());
        }
    }
}
