<?php

namespace Tests\Unit\Controllers;

use App\Exceptions\PageException;
use App\Http\Controllers\Page\DeletePageController;
use App\Models\Page;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePageControllerTest extends TestCase
{
    private DeletePageController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new DeletePageController;
        Storage::fake('s3');
    }

    #[Test]
    public function it_deletes_image_from_s3_if_exists_and_page()
    {
        Log::shouldReceive('debug')->atLeast()->once();
        $imagePath = 'pages/test-image.jpg';

        $page = $this->createMockPage($imagePath);
        $page->shouldReceive('delete')->once()->andReturn(true);

        $response = ($this->controller)($page);

        Storage::disk('s3')->assertMissing($imagePath);
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_does_not_call_s3_if_image_is_null()
    {
        $page = $this->createMockPage(null);

        $page->shouldReceive('delete')->once()->andReturn(true);

        ($this->controller)($page);

        $this->assertEmpty(Storage::disk('s3')->allFiles());
    }

    #[Test]
    public function it_throws_page_exception_if_delete_fails()
    {
        $page = $this->createMockPage(null);
        $page->shouldReceive('delete')
            ->andThrow(new Exception('Database error'));

        $this->expectException(PageException::class);
        $this->expectExceptionMessage('Database error');

        ($this->controller)($page);
    }

    private function createMockPage(?string $image): MockInterface|Page
    {
        $page = Mockery::mock(Page::class)->makePartial();
        $page->image = $image;

        return $page;
    }
}
