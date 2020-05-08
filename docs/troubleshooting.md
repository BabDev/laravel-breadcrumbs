# Troubleshooting

## General

- Re-read the documentation and make sure you did everything correctly
- Start with the simple options and only use the advanced options (e.g. Route-Bound Breadcrumbs) once you understand how it works

## Class 'Breadcrumbs' not found

- Try running `php artisan package:discover` to ensure the service provider is detected by Laravel

## Breadcrumb not found with name ...

- Make sure you register the breadcrumbs in the right place (`routes/breadcrumbs.php` by default)
    - Try putting `dd(__FILE__)` in the file to make sure it's loaded.
    - Try putting `dd($files)` in `BreadcrumbsServiceProvider::registerManager()` to check the path is correct
    - If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the path(s) in `config/breadcrumbs.php`
- Make sure the breadcrumb name is correct
    - If using Route-Bound Breadcrumbs, make sure it matches the route name
- To suppress these errors when using Route-Bound Breadcrumbs (if you don't want breadcrumbs on some pages), either:
    - Register them with an empty closure (no push/parent calls), or
    - Set `missing-route-bound-breadcrumb-exception` to `false` in the config file to disable the check (but you won't be warned if you miss any pages)

## BreadcrumbsServiceProvider Throws FileNotFoundException

- Make sure the path is correct
- If so, check the file ownership & permissions are correct
- If not, try running `php artisan config:clear` (or manually delete `bootstrap/cache/config.php`) or update the path(s) in `config/breadcrumbs.php`

## Undefined variable: breadcrumbs in view file

- Make sure you use `{{ Breadcrumbs::render() }}` or `{{ Breadcrumbs::view() }}`, not `@include()`
    - If you do manually `@include()` a breadcrumbs view, ensure you pass the breadcrumb collection into it
