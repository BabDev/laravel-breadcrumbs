<?php

namespace BabDev\Breadcrumbs\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Support\Str;

/**
 * Exception that is thrown if the user attempts to register two breadcrumbs with the same name.
 */
class DuplicateBreadcrumbException extends \InvalidArgumentException implements BreadcrumbsException, ProvidesSolution
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Breadcrumb name "%s" has already been registered', $name));

        $this->name = $name;
    }

    public function getSolution(): Solution
    {
        // Determine the breadcrumbs file name(s)
        $files = (array) config('breadcrumbs.files');

        $basePath = base_path() . \DIRECTORY_SEPARATOR;

        foreach ($files as &$file) {
            $file = Str::replaceFirst($basePath, '', $file);
        }

        if (\count($files) > 1) {
            $description = \sprintf('Look in the following files for multiple breadcrumbs named `%s`: %s', $this->name, \implode(', ', $files));
        } elseif (\count($files) === 1) {
            $description = \sprintf('Look in `%s` for multiple breadcrumbs named `%s`.', $files[0], $this->name);
        } else {
            $description = \sprintf('Check your application for multiple breadcrumbs named `%s`.', $this->name);
        }

        return BaseSolution::create('Remove the duplicate breadcrumb')
            ->setSolutionDescription($description)
            ->setDocumentationLinks(
                [
                    'Defining breadcrumbs' => 'https://github.com/BabDev/laravel-breadcrumbs#defining-breadcrumbs',
                    'Laravel Breadcrumbs documentation' => 'https://github.com/BabDev/laravel-breadcrumbs#laravel-breadcrumbs',
                ]
            );
    }
}
