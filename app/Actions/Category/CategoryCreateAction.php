<?php

namespace App\Actions\Category;

use App\Http\Requests\Category\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryCreateAction
{
    public function handle(CategoryRequest $request): Category
    {
        $data = $request->validated();

        $category = Category::create($data);

        Log::debug('Category was created with id :', [$category->id]);

        return $category;
    }
}
