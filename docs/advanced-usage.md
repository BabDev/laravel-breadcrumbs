# Advanced Usage

## Breadcrumbs With No URL

The second parameter to `BreadcrumbsGenerator::push()` is optional, so if you want a breadcrumb with no URL you can do so:

```php
Breadcrumbs::for('sample', static function (BreadcrumbsGenerator $trail): void {
     $trail->push('Sample');
});
```

The `$breadcrumb->url` value will be `null`.

The default Bootstrap 4 template will treat this as an active breadcrumb and style it as such (i.e. adding the "active" class).

## Custom Data

The `BreadcrumbsGenerator::push()` method accepts an optional third parameter - `$data` - an array of arbitrary data to be passed to the breadcrumb which you can use in your custom template. For example, if you wanted each breadcrumb to have an icon, you could do the following:

```php
Breadcrumbs::for('home', static function (BreadcrumbsGenerator $trail): void {
     $trail->push('Home', route('home'), ['icon' => 'home.png']);
});
```

The `$data` array's entries will be merged into the breadcrumb as properties, so you would access the icon as `$breadcrumb->icon` in your template like this:

```blade
<li>
    <a href="{{ $breadcrumb->url }}">
        <img src="{{ asset('/images/icons/'.$breadcrumb->icon) }}">
        {{ $breadcrumb->title }}
    </a>
</li>
```

<div class="docs-note">The "title" and "url" keys are used by the generator for the title and URL defined in your <code>$trail->push()</code> calls, ensure you do not set these keys in your code.</div>

## Event Listeners

You can register event listeners to act before and after a breadcrumb is generated.

### Events

The following events are available when generating breadcrumbs:

- `BabDev\Breadcrumbs\Events\BeforeBreadcrumbGenerated` - Fired before the generator starts generating breadcrumbs (executing your registered callback functions)
- `BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated` - Fired after the generator finishes generating breadcrumbs

The events include three properties:

- `$breadcrumbs` - The current `BabDev\Breadcrumbs\Contracts\BreadcrumbsGenerator` instance
- `$name` - The name of the breadcrumb geing generated (either explicitly set in a `Breadcrumbs::render()` call or the route name if using [Route-Bound Breadcrumbs](/open-source/packages/laravel-breadcrumbs/docs/1.x/route-bound-breadcrumbs))
- `$params` - The additional parameters to be passed to the breadcrumb callbacks (either explicitly set in a `Breadcrumbs::render()` call or the route parameters if using [Route-Bound Breadcrumbs](/open-source/packages/laravel-breadcrumbs/docs/1.x/route-bound-breadcrumbs))

### Breadcrumb For Pagination

An example use case for this is to dynamically register a breadcrumb for a paginated view. To automatically add the current page number at the end, you will need to register an event listener in your application for the `BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated` event:

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

Then, you will need to create your listener (you can use the `artisan make:listener` command to help with this):

```php
<?php

namespace App\Listeners;

use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;

final class AppendPageNumberToBreadcrumbs
{
    public function handle(AfterBreadcrumbGenerated $event): void
    {
        $paginatedBreadcrumbs = ['category'];

        if (!in_array($event->name, $paginatedBreadcrumbs)) {
            return;
        }

        $page = (int) request('page', 1);

        if ($page > 1) {
            $event->breadcrumbs->push("Page $page");
        }
    }
}
```

This listener will ensure that a breadcrumb is added for page 2 (or any later page) on a paginated list and only for the breadcrumbs specified.

## Getting The Current Page Breadcrumb

To get the last breadcrumb for the current page, use `Breadcrumb::current()`. For example, you could use this to output the current page title:

```blade
<title>{{ ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Fallback Title' }}</title>
```

To ignore a breadcrumb, add `'current' => false` to the `$data` parameter in `BreadcrumbsGenerator::push()`. This can be useful to ignore pagination breadcrumbs:

```php
<?php

namespace App\Listeners;

use BabDev\Breadcrumbs\Events\AfterBreadcrumbGenerated;

final class AppendPageNumberToBreadcrumbs
{
    public function handle(AfterBreadcrumbGenerated $event): void
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

For more advanced filtering, you can call `Breadcrumbs::generate()` and use Laravel's [Collection](https://laravel.com/docs/collections) class methods instead.

```php
$current = Breadcrumbs::generate()->where('current', '!==', false)->last();
```

## Switching Views At Runtime

You can use `Breadcrumbs::view()` in place of `Breadcrumbs::render()` to render a template other than the configured default.

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

## Overriding The "Current" Route

If you call `Breadcrumbs::render()` or `Breadcrumbs::generate()` with no parameters, it will use the current route name and parameters by default (as returned by the router's `current()` method).

You can override this by calling `Breadcrumbs::setCurrentRoute($name, ...$params)`.

## Checking If A Breadcrumb Exists

To check if a breadcrumb with a given name exists, call `Breadcrumbs::exists($name)`.

## Defining Breadcrumbs In A Different File

If you don't want to use `routes/breadcrumbs.php`, you can change it in the config file. Ensure you have published the package configuration, then open `config/breadcrumbs.php` and edit this line:

```php
    'files' => base_path('routes/breadcrumbs.php'),
```

It can be an absolute path as above, or an array:

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

## Defining Breadcrumbs At Runtime

Another option for defining breadcrumbs is to add them to the `BreadcrumbsManager` at runtime anywhere in your application. This package uses an "afterResolving" callback on the manager service to load the configured files from your application. You could choose to do something similar, or register breadcrumbs dynamically in a controller (as an example).

## Macros

The BreadcrumbsManager class is macroable, so you can add your own methods. For example:

```php
Breadcrumbs::macro('pageTitle', function (): string {
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

## Advanced Customizations

For more advanced customizations you can create your own version of the generator and/or manager contracts and optionally change the container bindings.
