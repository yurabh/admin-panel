<?php

namespace Tests\Feature\Post;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        config(['cache.default' => 'array']);
    }

    #[Test]
    public function test_store_creates_post_with_tags_and_logs_correct_id(): void
    {
        Log::spy();
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $tagIds = $tags->pluck('id')->toArray();

        $payload = [
            'title' => 'Senior Post Title',
            'content' => 'High quality content for testing.',
            'slug' => 'senior-post-title',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'tags' => $tagIds,
            'is_published' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/admin/posts', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'slug', 'content', 'is_published',
                    'category' => ['id', 'name', 'slug'],
                    'tags' => [['id', 'name', 'slug']],
                    'author' => ['id', 'name'],
                ],
            ]);

        $postId = $response->json('data.id');

        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'category_id' => $category->id,
            'is_published' => true,
        ]);

        foreach ($tagIds as $id) {
            $this->assertDatabaseHas('post_tag', [
                'post_id' => $postId,
                'tag_id' => $id,
            ]);
        }

        Log::shouldHaveReceived('debug')
            ->with('Post stored with id: '.$postId)
            ->once();
    }

    #[Test]
    public function test_store_validation_fails_if_required_fields_missing(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/admin/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'slug']);
    }

    #[Test]
    public function test_store_unauthorized_for_non_admin(): void
    {
        $this->user->role = UserRole::USER;

        $this->actingAs($this->user)
            ->postJson('/api/admin/posts', ['title' => 'Test'])
            ->assertForbidden();
    }
}
