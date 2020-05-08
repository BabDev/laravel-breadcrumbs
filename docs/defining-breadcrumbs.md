# Defining Breadcrumbs

Breadcrumbs will usually correspond to actions or types of pages. For each breadcrumb, at a minimum you must specify a name and the breadcrumb title. You should also define the URL that the breadcrumb links to if it has a corresponding page. Since these are likely to change dynamically, breadcrumbs are registered in callback functions where you may pass any data you need.

## Static pages

The most simple breadcrumb is probably going to be your homepage, which will look something like this:

```php
Breadcrumbs::for('home', static function (BreadcrumbsGenerator $trail): void {
    $trail->push('Home', route('home'));
});
```

As you can see, you call `$trail->push($title, $url)` inside the Closure to add the breadcrumb to the list.

For generating the URL, you can use any of Laravel's [URL generating functions](https://laravel.com/docs/urls) or provide a URL.

This example would be rendered like this:

```blade
{{ Breadcrumbs::render('home') }}
```

## Parent links

This is another static page, but this has a parent link before it:

```php
Breadcrumbs::for('blog', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});
```

It works by calling the callback for the `home` breadcrumb defined above and adding any additional items afterwards.

It would be rendered like this:

```blade
{{ Breadcrumbs::render('blog') }}
```

Note that the default templates do not create a link for the last breadcrumb (the one for the current page) even when a URL is specified. You can override this by creating your own template. See the [Custom Templates](/open-source/packages/laravel-breadcrumbs/docs/1.x/custom-templates) page for more details.

## Dynamic Titles & Links

This is a dynamically generated page pulled from the database:

```php
Breadcrumbs::for('post', static function (BreadcrumbsGenerator $trail, Post $post): void {
    $trail->parent('blog');
    $trail->push($post->title, route('post', $post));
});
```

The `$post` object (probably an [Eloquent](https://laravel.com/docs/eloquent) model, but could be anything) would be passed in from the view:

```blade
{{ Breadcrumbs::render('post', $post) }}
```

{tip} You can pass as many parameters as desired.

## Nested Categories

Finally, if you have nested categories or other special requirements, you can call `$trail->push()` multiple times:

```php
Breadcrumbs::for('category', static function (BreadcrumbsGenerator $trail, Category $category): void {
    $trail->parent('blog');

    foreach ($category->ancestors as $ancestor) {
        $trail->push($ancestor->title, route('category', $ancestor->id));
    }

    $trail->push($category->title, route('category', $category->id));
});
```

Alternatively you could make a recursive function such as this:

```php
Breadcrumbs::for('category', static function (BreadcrumbsGenerator $trail, Category $category): void {
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
