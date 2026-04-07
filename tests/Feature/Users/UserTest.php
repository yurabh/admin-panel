<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function test_index_returns_users_with_relations(): void
    {
        User::factory()->hasPosts(1)->hasComments(1)->create();
        $response = $this->actingAs($this->admin,'sanctum')
            ->getJson('/api/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'posts',
                        'role',
                        'created_at',
                        'comments',
                        'pages',
                        'posts'
                    ]
                ]
            ]);

        $usersCollection = $response->original;
        $this->assertTrue($usersCollection->first()->relationLoaded('posts'), 'Posts relation not loaded');
        $this->assertTrue($usersCollection->first()->relationLoaded('comments'), 'Comments relation not loaded');
        $this->assertTrue($usersCollection->first()->relationLoaded('pages'), 'Pages relation not loaded');
    }

    #[Test]
    public function test_index_returns_401_if_unauthenticated(): void
    {
        $this->getJson('/api/admin/users')
            ->assertUnauthorized();
    }
}
