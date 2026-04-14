<?php

namespace Actions;

use App\Actions\Category\CategoryUpdateAction;
use App\Http\Requests\Category\CategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    private CategoryUpdateAction $action;

    private Category $category;

    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CategoryUpdateAction;

        $this->category = Category::factory(1)->createOne([
            'name' => 'Old Tech',
            'slug' => 'old-tech',
        ]);

        $this->data = [
            'name' => 'New AI Technology',
            'slug' => 'new-ai-technology',
        ];
    }

    #[Test]
    public function it_updates_category_name_and_slug_correctly(): void
    {
        $request = $this->createMockRequest($this->data);

        $result = $this->action->handle($request, $this->category);

        $this->assertDatabaseHas('categories', $this->data);

        $this->assertEquals($this->data, $result->only(['name', 'slug']));
    }

    #[Test]
    public function it_logs_message_when_category_is_updated(): void
    {
        Log::shouldReceive('debug')
            ->once()
            ->with('Category was updated with id: ', ['category' => $this->category->id]);

        $request = $this->createMockRequest($this->data);

        $this->action->handle($request, $this->category);
    }

    #[Test]
    public function it_returns_the_updated_category_instance(): void
    {
        $request = $this->createMockRequest($this->data);

        $result = $this->action->handle($request, $this->category);

        $this->assertTrue($this->category->is($result));
    }

    private function createMockRequest(array $data): MockInterface|CategoryRequest
    {
        return $this->partialMock(CategoryRequest::class, function ($mock) use ($data) {
            $mock->shouldReceive('validated')->andReturn($data);
        });
    }
}
