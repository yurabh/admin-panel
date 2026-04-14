<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    #[Test]
    public function it_transforms_category_model_into_correct_array_test(): void
    {
        $category = $this->createCategory();

        $category->setRelation('posts', collect());

        $resource = new CategoryResource($category);

        $result = $resource->resolve();

        $this->assertEquals(1, $result['id']);

        $this->assertEquals('Tech News', $result['name']);

        $this->assertEquals('tech-news', $result['slug']);

        $this->assertEquals($category->created_at->format('d.m.Y'), $result['created_at']);

        $this->assertArrayHasKey('posts', $result);
    }

    private function createCategory(): Category
    {
        $category = new Category;
        $category->id = 1;
        $category->name = 'Tech News';
        $category->slug = 'tech-news';
        $category->created_at = now();

        return $category;
    }
}
