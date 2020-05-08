# Frequently Asked Questions

## Why is there no `Breadcrumbs::resource()` method?

In the previous package, it was suggested to add a `Breadcrumbs::resource()` method to match [`Route::resource()`](https://laravel.com/docs/controllers#resource-controllers), but no one had come up with a good implementation that (a) is flexible enough to deal with translations, nested resources, etc., and (b) isn't overly complex as a result.

In your own application, you can add your own method using `Breadcrumbs::macro()`. Here's a starting point:

```php
Breadcrumbs::macro('resource', function (string $name, string $title): void {
    // Home > Blog
    Breadcrumbs::for("$name.index", static function (BreadcrumbsGenerator $trail) use ($name, $title): void {
        $trail->parent('home');
        $trail->push($title, route("$name.index"));
    });

    // Home > Blog > New
    Breadcrumbs::for("$name.create", static function (BreadcrumbsGenerator $trail) use ($name): void {
        $trail->parent("$name.index");
        $trail->push('New', route("$name.create"));
    });

    // Home > Blog > Post 123
    Breadcrumbs::for("$name.show", static function (BreadcrumbsGenerator $trail, $model) use ($name): void {
        $trail->parent("$name.index");
        $trail->push($model->title, route("$name.show", $model));
    });

    // Home > Blog > Post 123 > Edit
    Breadcrumbs::for("$name.edit", static function (BreadcrumbsGenerator $trail, $model) use ($name): void {
        $trail->parent("$name.show", $model);
        $trail->push('Edit', route("$name.edit", $model));
    });
});

Breadcrumbs::resource('blog', 'Blog');
Breadcrumbs::resource('photos', 'Photos');
Breadcrumbs::resource('users', 'Users');
```

Note that this *doesn't* deal with translations or nested resources, and it assumes that all models have a `title` attribute. Adapt it however you see fit.
