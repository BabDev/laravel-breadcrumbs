# Migrate from `davejamesmiller/laravel-breadcrumbs` to `babdev/laravel-breadcrumbs`

The below guide will assist in migrating from `davejamesmiller/laravel-breadcrumbs` to `babdev/laravel-breadcrumbs`

## Change Composer package

You will need to change the Composer package.

You can do this by editing your `composer.json`, replacing `"davejamesmiller/laravel-breadcrumbs": "*"` (where `*` is your version constraint) with `"babdev/laravel-breadcrumbs": "^1.0"`.

Or, you can use the following Composer commands to replace the package:

```sh
composer remove davejamesmiller/laravel-breadcrumbs
composer require babdev/laravel-breadcrumbs
```

## Update Configuration

If you have published the configuration from the old package, you will need to update your application's configuration to match the version from this package.

`diff config/breadcrumbs.php vendor/babdev/laravel-breadcrumbs/config/breadcrumbs.php` can show you the differences between files.

## Update Namespace References

You will need to update any references in your application to use this package's namespace.

Search for all references to `DaveJamesMiller\Breadcrumbs` and replace those with `BabDev\Breadcrumbs`.

## Replace `BreadcrumbsManager::register()` calls

The `BreadcrumbsManager::register()` method (or `Breadcrumbs::register()` if using the facade) has been removed, use `BreadcrumbsManager::for()` instead.

## Replace Before/After Callbacks With Events

The `BreadcrumbsManager::before()` and `BreadcrumbsManager::after()` method have been removed in favor of using events (which support additional parameters not available to the old callbacks).

Please see the "Event Listeners" section of the [README](./README.md) for more details about this.
