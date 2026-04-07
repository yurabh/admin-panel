<?php

namespace App\Actions\Category;

use App\Http\Requests\Category\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryUpdateAction
{
    public function handle(CategoryRequest $request, Category $category): Category
    {
        $data = $request->validated();

        $category->update($data);

        Log::debug('Category was updated with id: ', ['category' => $category->id]);

        return $category;
    }
}
