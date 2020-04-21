<?php

namespace App\Http\Controllers;

use BabDev\Breadcrumbs\Tests\Models\Post;
use Breadcrumbs;

class PostController
{
    public function edit(Post $post)
    {
        return Breadcrumbs::render();
    }
}
