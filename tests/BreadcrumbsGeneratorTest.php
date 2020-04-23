<?php

namespace BabDev\Breadcrumbs\Tests;

use BabDev\Breadcrumbs\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator as BreadcrumbsGeneratorContract;
use BabDev\Breadcrumbs\Exceptions\InvalidBreadcrumbException;
use Orchestra\Testbench\TestCase;

class BreadcrumbsGeneratorTest extends TestCase
{
    public function testGeneratesABreadcrumb(): void
    {
        $callbacks = [
            'blog' => static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->push('Blog', '/blog');
            },
        ];

        $breadcrumbs = (new BreadcrumbsGenerator())->generate($callbacks, [], [], 'blog', []);

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals(
            [
                (object) [
                    'title' => 'Blog',
                    'url' => '/blog',
                ],
            ],
            $breadcrumbs->toArray()
        );
    }

    public function testGeneratesABreadcrumbWithBeforeAndAfterCallbacks(): void
    {
        $callbacks = [
            'blog' => static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->push('Blog', '/blog');
            },
        ];

        $beforeCallbacks = [
            static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->push('Home', '/');
            },
        ];

        $afterCallbacks = [
            static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->push('Page 2', '/page-2');
            },
        ];

        $breadcrumbs = (new BreadcrumbsGenerator())->generate($callbacks, $beforeCallbacks, $afterCallbacks, 'blog', []);

        $this->assertCount(3, $breadcrumbs);
        $this->assertEquals(
            [
                (object) [
                    'title' => 'Home',
                    'url' => '/',
                ],
                (object) [
                    'title' => 'Blog',
                    'url' => '/blog',
                ],
                (object) [
                    'title' => 'Page 2',
                    'url' => '/page-2',
                ],
            ],
            $breadcrumbs->toArray()
        );
    }

    public function testGeneratesABreadcrumbWithAParent(): void
    {
        $callbacks = [
            'home' => static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->push('Home', '/');
            },
            'blog' => static function (BreadcrumbsGeneratorContract $trail): void {
                $trail->parent('home');
                $trail->push('Blog', '/blog');
            },
        ];

        $breadcrumbs = (new BreadcrumbsGenerator())->generate($callbacks, [], [], 'blog', []);

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals(
            [
                (object) [
                    'title' => 'Home',
                    'url' => '/',
                ],
                (object) [
                    'title' => 'Blog',
                    'url' => '/blog',
                ],
            ],
            $breadcrumbs->toArray()
        );
    }

    public function testDoesNotGenerateABreadcrumbForAnUnknownName(): void
    {
        $this->expectException(InvalidBreadcrumbException::class);

        (new BreadcrumbsGenerator())->generate([], [], [], 'blog', []);
    }
}
