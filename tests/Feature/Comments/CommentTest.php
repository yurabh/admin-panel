<?php

namespace Comments;

use App\Enums\UserRole;
use App\Events\NewCommentEvent;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private readonly User $user;
    private readonly Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->user = User::factory()->create([
            'role' => UserRole::USER,
        ]);

        $this->post = Post::factory()->create([
            'title' => 'Test Post Title',
            'slug' => 'test-post-slug',
            'content' => 'Test content body',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function test_store_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/comments', [
                'post_id' => $this->post->id,
                'user_id' => $this->user->id,
                'content' => $content = fake()->paragraph(),
                'is_approved' => true,
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'content', 'is_approved', 'user', 'post']
            ]);

        $commentId = $response->json('data.id');

        $this->assertDatabaseHas('comments', [
            'id' => $commentId,
            'content' => $content,
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);

        Event::assertDispatched(NewCommentEvent::class, function ($event) use ($commentId) {
            return $event->comment->id === $commentId;
        });
    }

    #[Test]
    public function test_show_successfully(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'content' => 'Sample comment content'
        ]);

        Log::shouldReceive('debug')->atLeast()->once();

        $this->actingAs($this->user)
            ->getJson("/api/comments/{$comment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $comment->id)
            ->assertJsonPath('data.content', $comment->content);
    }

    #[Test]
    public function test_destroy_successfully(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
        ]);

        Log::shouldReceive('debug')->atLeast()->twice();

        $this->actingAs($this->user)
            ->deleteJson("/api/comments/{$comment->id}")
            ->assertOk();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function test_store_validation_fails(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/comments', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['post_id', 'content']);
    }
}
