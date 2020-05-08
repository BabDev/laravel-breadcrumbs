# Installation & Setup

To install this package, run the following [Composer](https://getcomposer.org/) command:

```bash
composer require babdev/laravel-breadcrumbs
```

## Register The Package

If your application is not using package discovery, you will need to add the service provider to your `config/app.php` file.

```php
return [
    'providers' => [
        BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider::class,
    ],
];
```

To use the facade, you will also need to register it in your `config/app.php` file.

```php
return [
    'aliases' => [
        'Breadcrumbs' => BabDev\Breadcrumbs\Facades\Breadcrumbs::class,
    ],
];
```

## Publish Resources

If you need to customize the package configuration, you can publish it to your application's `config` directory with the following command:

```bash
php artisan vendor:publish --provider="BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider" --tag="config"
```

If you need to customize the package views, you can publish them to your application's `resources/views` directory with the following command:

```bash
php artisan vendor:publish --provider="BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider" --tag="views"
```

## Setup

### Defining Your Breadcrumbs

To get started with this package, you will need to create a file which defines your application's breadcrumbs. By default, this package looks for a `routes/breadcrumbs.php` file, but you may customize this in your configuration.

A typical file adds callbacks to the manager service which defines a breadcrumb, and looks like this:

```php
<?php

use App\Models\Category;
use App\Models\Post;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator;
use BabDev\Breadcrumbs\Contracts\BreadcrumbsManager;

/*
 * You may use either the Breadcrumbs facade or the $breadcrumbs variable in this file,
 * both of which reference the `BreadcrumbsManager` service
 */

/** @var $breadcrumbs BreadcrumbsManager */

// Home
Breadcrumbs::for('home', static function (BreadcrumbsGenerator $trail): void {
    $trail->push('Home', route('home'));
});

// Home > About
Breadcrumbs::for('about', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('home');
    $trail->push('About', route('about'));
});

// Home > Blog
Breadcrumbs::for('blog', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});

// Home > Blog > [Category]
Breadcrumbs::for('category', static function (BreadcrumbsGenerator $trail, Category $category): void {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category->id));
});

// Home > Blog > [Category] > [Post]
Breadcrumbs::for('post', static function (BreadcrumbsGenerator $trail, Post $post): void {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post->id));
});
```

Please see the [Defining Breadcrumbs](/open-source/packages/laravel-breadcrumbs/docs/1.x/defining-breadcrumbs) page for more details on configuring your application's breadcrumb trails.

### Choose A Template

Similar to the Laravel UI package, a [Bootstrap 4](https://getbootstrap.com/docs/4.4/components/breadcrumb/) compatible ordered list will be rendered, so if you're using Bootstrap 4 you can skip this step.

If you are using another CSS framework, you will need to define a view compatible with your framework/template. Before defining this new view, you will need to publish the package configuration as noted above.

Once published, open the `config/breadcrumbs.php` and edit this line:

```php
    'view' => 'breadcrumbs::bootstrap4',
```

Please see the [Custom Templates](/open-source/packages/laravel-breadcrumbs/docs/1.x/custom-templates) page for more details on defining a breadcrumbs view.

### Output The Breadcrumbs

Finally, call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional parameters.

```blade
{{ Breadcrumbs::render('home') }}

{{ Breadcrumbs::render('category', $category) }}
```

Please see the [Outputting Breadcrumbs](/open-source/packages/laravel-breadcrumbs/docs/1.x/outputting-breadcrumbs) page for other output options, and the [Route-Bound Breadcrumbs](/open-source/packages/laravel-breadcrumbs/docs/1.x/route-bound-breadcrumbs) page for details on linking breadcrumb names to route names automatically.
