# Laravel Breadcrumbs

[![Latest Stable Version](https://poser.pugx.org/babdev/laravel-breadcrumbs/v/stable)](https://packagist.org/packages/babdev/laravel-breadcrumbs) [![Latest Unstable Version](https://poser.pugx.org/babdev/laravel-breadcrumbs/v/unstable)](https://packagist.org/packages/babdev/laravel-breadcrumbs) [![Total Downloads](https://poser.pugx.org/babdev/laravel-breadcrumbs/downloads)](https://packagist.org/packages/babdev/laravel-breadcrumbs) [![License](https://poser.pugx.org/babdev/laravel-breadcrumbs/license)](https://packagist.org/packages/babdev/laravel-breadcrumbs) [![Build Status](https://travis-ci.com/BabDev/laravel-breadcrumbs.svg?branch=master)](https://travis-ci.com/BabDev/laravel-breadcrumbs)

A simple [Laravel](https://laravel.com) style way to create breadcrumbs.

This package is a continuation of the [davejamesmiller/laravel-breadcrumbs](https://github.com/davejamesmiller/laravel-breadcrumbs) package.

## Installation

To install this package, run the following [Composer](https://getcomposer.org/) command:

```sh
composer require babdev/laravel-breadcrumbs
```

If your application is not using package discovery, you will need to add the service provider to your `config/app.php` file:

```sh
BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider::class,
```

Likewise, you will also need to register the facade in your `config/app.php` file if not using package discovery:

```sh
'Breadcrumbs' => BabDev\Breadcrumbs\Facades\Breadcrumbs::class,
```

## Configuration

If you need to customize the package configuration, you can publish it to your application's `config` directory with the following command:

```sh
php artisan vendor:publish --provider="BabDev\Breadcrumbs\Providers\BreadcrumbsServiceProvider" --tag="config"
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
Breadcrumbs::for('home', function (BreadcrumbsGenerator $trail) {
    $trail->push('Home', route('home'));
});

// Home > About
Breadcrumbs::for('about', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('About', route('about'));
});

// Home > Blog
Breadcrumbs::for('blog', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});

// Home > Blog > [Category]
Breadcrumbs::for('category', function (BreadcrumbsGenerator $trail, Category $category) {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category->id));
});

// Home > Blog > [Category] > [Post]
Breadcrumbs::for('post', function (BreadcrumbsGenerator $trail, Post $post) {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post->id));
});
```

See the [Defining Breadcrumbs](#defining-breadcrumbs) section for more details.

### Choose A Template

Similar to the Laravel UI package, a [Bootstrap 4](https://getbootstrap.com/docs/4.4/components/breadcrumb/) compatible ordered list will be rendered, so if you're using Bootstrap 4 you can skip this step.

If you are using another CSS framework, you will need to define a view compatible with your framework/template. Before defining this new view, you will need to publish the package configuration as noted above.

Once published, open the `config/breadcrumbs.php` and edit this line:

```php
    'view' => 'breadcrumbs::bootstrap4',
```

See the [Custom Templates](#custom-templates) section for more details on defining a breadcrumbs view.

### Output The Breadcrumbs

Finally, call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional parameters – for example:

```blade
{{ Breadcrumbs::render('home') }}

{{ Breadcrumbs::render('category', $category) }}
```

See the [Outputting Breadcrumbs](#outputting-breadcrumbs) section for other output options, and see [Route-Bound Breadcrumbs](#route-bound-breadcrumbs) for a way to link breadcrumb names to route names automatically.


## Defining Breadcrumbs

Breadcrumbs will usually correspond to actions or types of page. For each breadcrumb you specify a name, the breadcrumb title and the URL to link it to. Since these are likely to change dynamically, you do this in a closure, and you pass any variables you need into the closure.

The following examples should make it clear:

### Static pages

The most simple breadcrumb is probably going to be your homepage, which will look something like this:

```php
Breadcrumbs::for('home', function (BreadcrumbsGenerator $trail) {
    $trail->push('Home', route('home'));
});
```

As you can see, you call `$trail->push($title, $url)` inside the closure.

For generating the URL, you can use any of the standard Laravel URL generation methods, including:

- `url('path/to/route')` (`URL::to()`)
- `secure_url('path/to/route')`
- `route('routename')` or `route('routename', 'param')` or `route('routename', ['param1', 'param2'])` (`URL::route()`)
- `action('controller@action')` (`URL::action()`)
- Or just pass a string URL (`'http://www.example.com/'`)

This example would be rendered like this:

```blade
{{ Breadcrumbs::render('home') }}
```

And results in this output:

> Home

### Parent links

This is another static page, but this has a parent link before it:

```php
Breadcrumbs::for('blog', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});
```

It works by calling the callback for the `home` breadcrumb defined above and adding any additional items afterwards.

It would be rendered like this:

```blade
{{ Breadcrumbs::render('blog') }}
```

And results in this output:

> [Home](#) / Blog

Note that the default templates do not create a link for the last breadcrumb (the one for the current page), even when a URL is specified. You can override this by creating your own template – see [Custom Templates](#custom-templates) for more details.

### Dynamic Titles & Links

This is a dynamically generated page pulled from the database:

```php
Breadcrumbs::for('post', function (BreadcrumbsGenerator $trail, Post $post) {
    $trail->parent('blog');
    $trail->push($post->title, route('post', $post));
});
```

The `$post` object (probably an [Eloquent](https://laravel.com/docs/eloquent) model, but could be anything) would be passed in from the view:

```blade
{{ Breadcrumbs::render('post', $post) }}
```

It results in this output:

> [Home](#) / [Blog](#) / Post Title

**Tip:** You can pass multiple parameters if necessary.

### Nested Categories

Finally, if you have nested categories or other special requirements, you can call `$trail->push()` multiple times:

```php
Breadcrumbs::for('category', function (BreadcrumbsGenerator $trail, Category $category) {
    $trail->parent('blog');

    foreach ($category->ancestors as $ancestor) {
        $trail->push($ancestor->title, route('category', $ancestor->id));
    }

    $trail->push($category->title, route('category', $category->id));
});
```

Alternatively you could make a recursive function such as this:

```php
Breadcrumbs::for('category', function (BreadcrumbsGenerator $trail, Category $category) {
    if ($category->parent) {
        $trail->parent('category', $category->parent);
    } else {
        $trail->parent('blog');
    }

    $trail->push($category->title, route('category', $category->slug));
});
```

Both would be rendered like this:

```blade
{{ Breadcrumbs::render('category', $category) }}
```

And result in this:

> [Home](#) / [Blog](#) / [Grandparent Category](#) / [Parent Category](#) / Category Title


## Custom Templates

### Create A View

To customize the HTML, create your own view file (e.g. `resources/views/partials/breadcrumbs.blade.php`).

```blade
@php /** @var \Illuminate\Support\Collection $breadcrumbs **/ @endphp
@if ($breadcrumbs->isNotEmpty())
    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
            @endif
        @endforeach
    </ol>
@endif
```

(See the [views/ directory](https://github.com/BabDev/laravel-breadcrumbs/tree/master/resources/views) for the built-in templates.)

#### View Data

The breadcrumb view receives one parameter, `$breadcrumbs`, which is a [Collection](https://laravel.com/docs/collections) instance.

Each breadcrumb is an object with the following keys:

- `title` – The breadcrumb title
- `url` – The breadcrumb URL, or `null` if none was given
- Plus additional keys for each item in `$data` (see [Custom data](#custom-data))

### Update The Configfiguration

Then update your config file (`config/breadcrumbs.php`) with the custom view name, e.g.:

```php
    'view' => 'partials.breadcrumbs', // resources/views/partials/breadcrumbs.blade.php
```

### Skipping The View

Alternatively you can skip the custom view and call `Breadcrumbs::generate()` to get the breadcrumbs collection directly:

```blade
@foreach (Breadcrumbs::generate('post', $post) as $breadcrumb)
    {{-- ... --}}
@endforeach
```

## Outputting Breadcrumbs

Call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional parameters.

### With Blade

In the page (e.g. `resources/views/home.blade.php`):

```blade
{{ Breadcrumbs::render('home') }}
```

Or with a parameter:

```blade
{{ Breadcrumbs::render('category', $category) }}
```

## Route-Bound Breadcrumbs

In normal usage you must call `Breadcrumbs::render($name, ...$params)` to render the breadcrumbs on every page. If you prefer, you can name your breadcrumbs the same as your routes and avoid this duplication.

### Name Your Routes

Make sure each of your routes has a name. For example (`routes/web.php`):

```php
// Home
Route::name('home')->get('/', 'HomeController@index');

// Home > [Post]
Route::name('post')->get('/post/{id}', 'PostController@show');
```

For more details see [Named Routes](https://laravel.com/docs/routing#named-routes) in the Laravel documentation.

### Name Your Breadcrumbs To Match

For each route, create a breadcrumb with the same name and parameters. For example (`routes/breadcrumbs.php`):

```php
// Home
Breadcrumbs::for('home', function (BreadcrumbsGenerator $trail) {
     $trail->push('Home', route('home'));
});

// Home > [Post]
Breadcrumbs::for('post', function (BreadcrumbsGenerator $trail, Post $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

To add breadcrumbs to a [custom 404 Not Found page](https://laravel.com/docs/errors#custom-http-error-pages), use the name `errors.404`:

```php
// Error 404
Breadcrumbs::for('errors.404', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('Page Not Found');
});
```

### Output Breadcrumbs In Your Layout

Call `Breadcrumbs::render()` with no parameters in your layout file (e.g. `resources/views/app.blade.php`):

```blade
{{ Breadcrumbs::render() }}
```

This will automatically output breadcrumbs corresponding to the current route. The same applies to `Breadcrumbs::generate()`:

```php
$breadcrumbs = Breadcrumbs::generate();
```

And to `Breadcrumbs::view()`:

```blade
{{ Breadcrumbs::view('breadcrumbs::bootstrap4') }}
```

### Route Binding Exceptions

It will throw an `InvalidBreadcrumbException` if the breadcrumb doesn't exist, to remind you to create one. To disable this (e.g. if you have some pages with no breadcrumbs), ensure you have published the package configuration, then open `config/breadcrumbs.php` and set this value:

```php
    'missing-route-bound-breadcrumb-exception' => false,
```

Similarly, to prevent it throwing an `UnnamedRouteException` if the current route doesn't have a name, set this value:

```php
    'unnamed-route-exception' => false,
```

### Route Model Binding

Laravel Breadcrumbs uses the same model binding as the controller. For example:

```php
// routes/web.php
Route::name('post')->get('/post/{post}', 'PostController@show');
```

```php
// app/Http/Controllers/PostController.php
use App\Models\Post;

class PostController extends Controller
{
    public function show(Post $post)
    {
        return view('post/show', ['post' => $post]);
    }
}
```

```php
// routes/breadcrumbs.php
Breadcrumbs::for('post', function (BreadcrumbsGenerator $trail, Post $post) {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

This makes your code less verbose and more efficient by only loading the post from the database once.

For more details see [Route Model Binding](https://laravel.com/docs/routing#route-model-binding) in the Laravel documentation.

### Resourceful Controllers

Laravel automatically creates route names for resourceful controllers, e.g. `photo.index`, which you can use when defining your breadcrumbs. For example:

```php
// routes/web.php
Route::resource('photos', PhotosController::class);
```

```sh
$ php artisan route:list
+--------+----------+---------------------+----------------+--------------------------+------------+
| Domain | Method   | URI                 | Name           | Action                   | Middleware |
+--------+----------+---------------------+----------------+--------------------------+------------+
|        | GET|HEAD | photos              | photos.index   | PhotosController@index   |            |
|        | GET|HEAD | photos/create       | photos.create  | PhotosController@create  |            |
|        | POST     | photos              | photos.store   | PhotosController@store   |            |
|        | GET|HEAD | photos/{photo}      | photos.show    | PhotosController@show    |            |
|        | GET|HEAD | photos/{photo}/edit | photos.edit    | PhotosController@edit    |            |
|        | PUT      | photos/{photo}      | photos.update  | PhotosController@update  |            |
|        | PATCH    | photos/{photo}      |                | PhotosController@update  |            |
|        | DELETE   | photos/{photo}      | photos.destroy | PhotosController@destroy |            |
+--------+----------+---------------------+----------------+--------------------------+------------+
```

```php
// routes/breadcrumbs.php

// Photos
Breadcrumbs::for('photos.index', function (BreadcrumbsGenerator $trail) {
    $trail->parent('home');
    $trail->push('Photos', route('photos.index'));
});

// Photos > Upload Photo
Breadcrumbs::for('photos.create', function (BreadcrumbsGenerator $trail) {
    $trail->parent('photos.index');
    $trail->push('Upload Photo', route('photos.create'));
});

// Photos > [Photo Name]
Breadcrumbs::for('photos.show', function (BreadcrumbsGenerator $trail, Photo $photo) {
    $trail->parent('photos.index');
    $trail->push($photo->title, route('photos.show', $photo->id));
});

// Photos > [Photo Name] > Edit Photo
Breadcrumbs::for('photos.edit', function (BreadcrumbsGenerator $trail, Photo $photo) {
    $trail->parent('photos.show', $photo);
    $trail->push('Edit Photo', route('photos.edit', $photo->id));
});
```

For more details see [Resource Controllers](https://laravel.com/docs/controllers#resource-controllers) in the Laravel documentation.

(Related FAQ: [Why is there no Breadcrumbs::resource() method?](#why-is-there-no-breadcrumbsresource-method).)

## Advanced Usage

### Breadcrumbs With No URL

The second parameter to `BreadcrumbsGenerator::push()` is optional, so if you want a breadcrumb with no URL you can do so:

```php
$trail->push('Sample');
```

The `$breadcrumb->url` value will be `null`.

The default Bootstrap 4 template will treat this as an active breadcrumb and style it as such (i.e. adding the "active" class).

### Custom Data

The `BreadcrumbsGenerator::push()` method accepts an optional third parameter, `$data` – an array of arbitrary data to be passed to the breadcrumb, which you can use in your custom template. For example, if you wanted each breadcrumb to have an icon, you could do:

```php
$trail->push('Home', '/', ['icon' => 'home.png']);
```

The `$data` array's entries will be merged into the breadcrumb as properties, so you would access the icon as `$breadcrumb->icon` in your template, like this:

```blade
<li>
    <a href="{{ $breadcrumb->url }}">
        <img src="{{ asset('/images/icons/'.$breadcrumb->icon) }}">
        {{ $breadcrumb->title }}
    </a>
</li>
```

Do not use the keys `title` or `url` as they will be overwritten.

### Event Listeners

You can register event listeners to act before and after a breadcrumb is generated. An example use case for this is to add breadcrumbs at the start/end of the trail. For example, to automatically add the current page number at the end:

```php
<?php

namespace App\Providers;

use App\Listeners\AppendPageNumberToBreadcrumbs;
use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AfterBreadcrumbGenerated::class => [
            AppendPageNumberToBreadcrumbs::class,
        ],
    ];
}
```

```php
<?php

namespace App\Listeners;

use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;

class AppendPageNumberToBreadcrumbs
{
    public function handle(AfterBreadcrumbGenerated $event)
    {
        $page = (int) request('page', 1);

        if ($page > 1) {
            $event->breadcrumbs->push("Page $page");
        }
    }
}
```

### Getting The Current Page Breadcrumb

To get the last breadcrumb for the current page, use `Breadcrumb::current()`. For example, you could use this to output the current page title:

```blade
<title>{{ ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Fallback Title' }}</title>
```

To ignore a breadcrumb, add `'current' => false` to the `$data` parameter in `BreadcrumbsGenerator::push()`. This can be useful to ignore pagination breadcrumbs:

```php
<?php

namespace App\Listeners;

use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;

class AppendPageNumberToBreadcrumbs
{
    public function handle(AfterBreadcrumbGenerated $event)
    {
        $page = (int) request('page', 1);

        if ($page > 1) {
            $event->breadcrumbs->push("Page $page", null, ['current' => false]);
        }
    }
}
```

```blade
<title>
    {{ ($breadcrumb = Breadcrumbs::current()) ? "$breadcrumb->title –" : '' }}
    {{ ($page = (int) request('page')) > 1 ? "Page $page –" : '' }}
    Demo App
</title>
```

For more advanced filtering, use `Breadcrumbs::generate()` and Laravel's Collection class methods instead:

```php
$current = Breadcrumbs::generate()->where('current', '!==', 'false)->last();
```

### Switching Views At Runtime

You can use `Breadcrumbs::view()` in place of `Breadcrumbs::render()` to render a template other than the [default one](#choose-a-template):

```blade
{{ Breadcrumbs::view('partials.breadcrumbs2', 'category', $category) }}
```

Or you can override the config setting to affect all future `render()` calls:

```php
Config::set('breadcrumbs.view', 'partials.breadcrumbs2');
```

```blade
{{ Breadcrumbs::render('category', $category) }}
```

Or you could call `Breadcrumbs::generate()` to get the breadcrumbs Collection and load the view manually:

```blade
@include('partials.breadcrumbs2', ['breadcrumbs' => Breadcrumbs::generate('category', $category)])
```

### Overriding The "Current" Route

If you call `Breadcrumbs::render()` or `Breadcrumbs::generate()` with no parameters, it will use the current route name and parameters by default (as returned by the router's `current()` method).

You can override this by calling `Breadcrumbs::setCurrentRoute($name, ...$params)`.

### Checking If A Breadcrumb Exists

To check if a breadcrumb with a given name exists, call `Breadcrumbs::exists('name')`, which returns a boolean value.

### Defining Breadcrumbs In A Different File

If you don't want to use `routes/breadcrumbs.php`, you can change it in the config file. Ensure you have published the package configuration, then open `config/breadcrumbs.php` and edit this line:

```php
    'files' => base_path('routes/breadcrumbs.php'),
```

It can be an absolute path, as above, or an array:

```php
    'files' => [
        base_path('breadcrumbs/admin.php'),
        base_path('breadcrumbs/frontend.php'),
    ],
```

You can also use `glob()` to automatically find files using a wildcard:

```php
    'files' => glob(base_path('breadcrumbs/*.php')),
```

Or return an empty array `[]` to disable loading.

### Defining Breadcrumbs At Runtime

Another option for defining breadcrumbs is to add them to the `BreadcrumbsManager` at runtime anywhere in your application. This package uses an "afterResolving" callback to load the configured files from your application. You could choose to do something similar, or register breadcrumbs dynamically in a controller (as an example).

### Macros

The BreadcrumbsManager class is macroable, so you can add your own methods. For example:

```php
Breadcrumbs::macro('pageTitle', function () {
    $title = ($breadcrumb = Breadcrumbs::current()) ? "{$breadcrumb->title} – " : '';

    if (($page = (int) request('page')) > 1) {
        $title .= "Page $page – ";
    }

    return $title . 'Demo App';
});
```

```blade
<title>{{ Breadcrumbs::pageTitle() }}</title>
```

### Advanced Customizations

For more advanced customizations you can create your own version of the generator and/or manager contracts and optionally change the container bindings.

## FAQ

### Why is there no Breadcrumbs::resource() method?

In the previous package, a few people have suggested adding `Breadcrumbs::resource()` to match [`Route::resource()`](https://laravel.com/docs/controllers#resource-controllers), but no one has come up with a good implementation that (a) is flexible enough to deal with translations, nested resources, etc., and (b) isn't overly complex as a result.

In your own application, you can add your own method using `Breadcrumbs::macro()`. Here's a starting point:

```php
Breadcrumbs::macro('resource', function ($name, $title) {
    // Home > Blog
    Breadcrumbs::for("$name.index", function (BreadcrumbsGenerator $trail) use ($name, $title) {
        $trail->parent('home');
        $trail->push($title, route("$name.index"));
    });

    // Home > Blog > New
    Breadcrumbs::for("$name.create", function (BreadcrumbsGenerator $trail) use ($name) {
        $trail->parent("$name.index");
        $trail->push('New', route("$name.create"));
    });

    // Home > Blog > Post 123
    Breadcrumbs::for("$name.show", function (BreadcrumbsGenerator $trail, $model) use ($name) {
        $trail->parent("$name.index");
        $trail->push($model->title, route("$name.show", $model));
    });

    // Home > Blog > Post 123 > Edit
    Breadcrumbs::for("$name.edit", function (BreadcrumbsGenerator $trail, $model) use ($name) {
        $trail->parent("$name.show", $model);
        $trail->push('Edit', route("$name.edit", $model));
    });
});

Breadcrumbs::resource('blog', 'Blog');
Breadcrumbs::resource('photos', 'Photos');
Breadcrumbs::resource('users', 'Users');
```

Note that this *doesn't* deal with translations or nested resources, and it assumes that all models have a `title` attribute. Adapt it however you see fit.

## Troubleshooting

### General

- Re-read the instructions and make sure you did everything correctly.
- Start with the simple options and only use the advanced options (e.g. Route-Bound Breadcrumbs) once you understand how it works.

### Class 'Breadcrumbs' not found

- Try running `composer update BabDev/laravel-breadcrumbs` to upgrade.
- Try running `php artisan package:discover` to ensure the service provider is detected by Laravel.

### Breadcrumb not found with name ...

- Make sure you register the breadcrumbs in the right place (`routes/breadcrumbs.php` by default).
    - Try putting `dd(__FILE__)` in the file to make sure it's loaded.
    - Try putting `dd($files)` in `BreadcrumbsServiceProvider::registerManager()` to check the path is correct.
    - If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the path(s) in `config/breadcrumbs.php`.
- Make sure the breadcrumb name is correct.
    - If using Route-Bound Breadcrumbs, make sure it matches the route name exactly.
- To suppress these errors when using Route-Bound Breadcrumbs (if you don't want breadcrumbs on some pages), either:
    - Register them with an empty closure (no push/parent calls), or
    - Set [`missing-route-bound-breadcrumb-exception` to `false`](#route-binding-exceptions) in the config file to disable the check (but you won't be warned if you miss any pages).

#### BreadcrumbsServiceProvider Throws FileNotFoundException

- Make sure the path is correct.
- If so, check the file ownership & permissions are correct.
- If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the path(s) in `config/breadcrumbs.php`.

#### Undefined variable: breadcrumbs

- Make sure you use `{{ Breadcrumbs::render() }}` or `{{ Breadcrumbs::view() }}`, not `@include()`.

## License

This package is licensed under the MIT License. See the [LICENSE file](/LICENSE) for full details.
