<?php

namespace BabDev\Breadcrumbs\Tests;

use Breadcrumbs;
use Generator;

class TemplatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Home (Normal link)
        Breadcrumbs::for('home', function ($trail) {
            $trail->push('Home', url('/'));
        });

        // Home > Blog (Not a link)
        Breadcrumbs::for('blog', function ($trail) {
            $trail->parent('home');
            $trail->push('Blog');
        });

        // Home > Blog > [Category] (Active page)
        Breadcrumbs::for('category', function ($trail, $category) {
            $trail->parent('blog');
            $trail->push($category->title, url("blog/category/{$category->id}"));
        });

        $this->category = (object) [
            'id' => 456,
            'title' => 'Sample Category',
        ];
    }

    public function viewProvider(): Generator
    {
        foreach (\glob(__DIR__ . '/../resources/views/*.blade.php') as $filename) {
            $name = \basename($filename, '.blade.php');
            yield $name => [$name];
        }
    }

    /** @dataProvider viewProvider */
    public function testView($view)
    {
        $html = Breadcrumbs::view("breadcrumbs::$view", 'category', $this->category)->toHtml();

        $this->assertMatchesXmlSnapshot($html);
    }
}
