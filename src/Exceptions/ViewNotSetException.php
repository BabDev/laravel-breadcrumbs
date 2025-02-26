<?php

namespace BabDev\Breadcrumbs\Exceptions;

/**
 * Exception that is thrown if the user attempts to render breadcrumbs without setting a view.
 */
class ViewNotSetException extends \RuntimeException implements BreadcrumbsException {}
