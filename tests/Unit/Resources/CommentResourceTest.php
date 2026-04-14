<?php

namespace Tests\Unit\Resources;

use App\Enums\UserRole;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentResourceTest extends TestCase
{
    #[Test]
    public function it_transforms_comment_model_into_correct_array(): void
    {
        $user = $this->createUser();
        $post = $this->createPost();
        $comment = $this->createComment();
        $comment->setRelation('user', $user);
        $comment->setRelation('post', $post);

        $resource = new CommentResource($comment);
        $result = $resource->resolve();

        $this->assertEquals([
            'id' => 100,
            'content' => 'Great post!',
            'is_approved' => true,
            'user' => [
                'id' => 1,
                'name' => 'Ivan',
                'role' => 'user',
            ],
            'post' => [
                'post_id' => 10,
                'post_title' => 'Laravel Tips',
                'created_at' => $comment->created_at->format('d.m.Y H:i'),
            ],
        ], $result);
    }

    private function createComment(): Comment
    {
        $comment = new Comment;
        $comment->id = 100;
        $comment->content = 'Great post!';
        $comment->is_approved = true;
        $comment->created_at = now();

        return $comment;
    }

    private function createPost(): Post
    {
        $post = new Post;
        $post->id = 10;
        $post->title = 'Laravel Tips';
        $post->created_at = now();

        return $post;
    }

    private function createUser(): User
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Ivan';
        $user->role = UserRole::USER;

        return $user;
    }
}
