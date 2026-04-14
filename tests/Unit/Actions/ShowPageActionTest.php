<?php

namespace Tests\Unit\Actions;

use App\Actions\Page\ShowPageAction;
use App\Exceptions\PageException;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowPageActionTest extends TestCase
{
    use RefreshDatabase;

    private ShowPageAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ShowPageAction;
    }

    #[Test]
    public function it_returns_page_if_found(): void
    {
        $user = User::factory()->create();

        $expected = [
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'Full content...',
            'is_published' => true,
            'user_id' => $user->id,
        ];

        $page = Page::factory()->create($expected);

        $result = $this->action->handle($page->id);

        $this->assertInstanceOf(Page::class, $result);

        $this->assertEquals($expected, $result->only(['title', 'slug', 'content', 'is_published', 'user_id']));
    }

    #[Test]
    public function it_logs_error_and_throws_page_exception_when_not_found(): void
    {
        $nonExistentId = 999;

        Log::shouldReceive('debug')->once();

        $this->expectException(PageException::class);

        $this->expectExceptionMessage("No query results for model [App\Models\Page] 999");

        $this->action->handle($nonExistentId);
    }
}
