# Custom Templates

## Create A View

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

### View Data

The breadcrumb view receives one parameter, `$breadcrumbs`, which is a [Collection](https://laravel.com/docs/collections) instance.

Each breadcrumb is an object with the following keys:

- `title` – The breadcrumb title
- `url` – The breadcrumb URL, or `null` if none was given
- Plus additional keys for each item in `$data` (see [Custom data](#custom-data))

## Update The Configfiguration

Then update your config file (`config/breadcrumbs.php`) with the custom view name. If you have not already, you will need to publish the package configuration.

```php
    'view' => 'partials.breadcrumbs', // resources/views/partials/breadcrumbs.blade.php
```

## Skipping The View

Alternatively you can skip the custom view and call `Breadcrumbs::generate()` to get the breadcrumbs collection directly:

```blade
@foreach (Breadcrumbs::generate('post', $post) as $breadcrumb)
    {{-- ... --}}
@endforeach
```
