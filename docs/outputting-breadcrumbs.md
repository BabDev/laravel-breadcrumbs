# Outputting Breadcrumbs

Call `Breadcrumbs::render()` in the view for each page, passing it the name of the breadcrumb to use and any additional parameters.

```blade
{{ Breadcrumbs::render('home') }}
```

Or with a parameter:

```blade
{{ Breadcrumbs::render('category', $category) }}
```
