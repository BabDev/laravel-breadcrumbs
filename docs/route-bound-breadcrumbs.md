# Route-Bound Breadcrumbs

In normal usage you must call `Breadcrumbs::render($name, ...$params)` to render the breadcrumbs on every page. If you prefer, you can name your breadcrumbs the same as your routes and avoid this duplication.

## Name Your Routes

Make sure each of your routes has a name. For example, inside your `routes/web.php` file:

```php
// Home
Route::name('home')->get('/', 'HomeController@index');

// Home > [Post]
Route::name('post')->get('/post/{id}', 'PostController@show');
```

For more details see [Named Routes](https://laravel.com/docs/routing#named-routes) in the Laravel documentation.

## Name Your Breadcrumbs To Match

For each route, create a breadcrumb with the same name and parameters. For example (`routes/breadcrumbs.php`):

```php
// Home
Breadcrumbs::for('home', static function (BreadcrumbsGenerator $trail): void {
     $trail->push('Home', route('home'));
});

// Home > [Post]
Breadcrumbs::for('post', static function (BreadcrumbsGenerator $trail, Post $post): void {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

To add breadcrumbs to a [custom 404 page](https://laravel.com/docs/errors#custom-http-error-pages), use the name `errors.404`:

```php
// Error 404
Breadcrumbs::for('errors.404', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('home');
    $trail->push('Page Not Found');
});
```

## Output Breadcrumbs In Your Layout

Call `Breadcrumbs::render()` with no parameters in your layout file:

```blade
{{ Breadcrumbs::render() }}
```

This will automatically output breadcrumbs corresponding to the current route. The same applies to `Breadcrumbs::generate()` and `Breadcrumbs::view()`.

## Route Binding Exceptions

The breadcrumbs generator will throw an `InvalidBreadcrumbException` if the breadcrumb doesn't exist. To disable this behavior (i.e. if you have some pages with no breadcrumbs), ensure you have published the package configuration, then open `config/breadcrumbs.php` and set this value:

```php
    'missing-route-bound-breadcrumb-exception' => false,
```

Similarly, to prevent it throwing an `UnnamedRouteException` if the current route doesn't have a name, set this value:

```php
    'unnamed-route-exception' => false,
```

## Route Model Binding

Laravel Breadcrumbs uses the same model binding as the controller. For example:

```php
// routes/web.php
Route::name('post')->get('/post/{post}', 'PostController@show');
```

```php
use App\Http\Controllers\Controller;
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
Breadcrumbs::for('post', static function (BreadcrumbsGenerator $trail, Post $post): void {
    $trail->parent('home');
    $trail->push($post->title, route('post', $post));
});
```

This makes your code less verbose and more efficient by only loading the post from the database once.

For more details see [Route Model Binding](https://laravel.com/docs/routing#route-model-binding) in the Laravel documentation.

## Resourceful Controllers

Laravel automatically creates route names for resourceful controllers, e.g. `photo.index`, which you can use when defining your breadcrumbs. For example:

```php
// routes/web.php
Route::resource('photos', PhotosController::class);
```

```bash
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
Breadcrumbs::for('photos.index', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('home');
    $trail->push('Photos', route('photos.index'));
});

// Photos > Upload Photo
Breadcrumbs::for('photos.create', static function (BreadcrumbsGenerator $trail): void {
    $trail->parent('photos.index');
    $trail->push('Upload Photo', route('photos.create'));
});

// Photos > [Photo Name]
Breadcrumbs::for('photos.show', static function (BreadcrumbsGenerator $trail, Photo $photo): void {
    $trail->parent('photos.index');
    $trail->push($photo->title, route('photos.show', $photo->id));
});

// Photos > [Photo Name] > Edit Photo
Breadcrumbs::for('photos.edit', static function (BreadcrumbsGenerator $trail, Photo $photo): void {
    $trail->parent('photos.show', $photo);
    $trail->push('Edit Photo', route('photos.edit', $photo->id));
});
```

For more details see [Resource Controllers](https://laravel.com/docs/controllers#resource-controllers) in the Laravel documentation.

(Related FAQ: [Why is there no Breadcrumbs::resource() method?](/open-source/packages/laravel-breadcrumbs/docs/1.x/faq).)
