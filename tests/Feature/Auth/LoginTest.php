<?php

namespace Tests\Feature\Auth;

use App\Jobs\NotifyAdminsAboutLoginJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    #[Test]
    public function test_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => $email = 'admin@example.com',
            'password' => Hash::make($password = 'secret-password'),
            'role' => 'admin',
        ]);

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                ],
            ])
            ->assertJsonPath('data.user.email', $email);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->id,
        ]);

        Queue::assertPushed(NotifyAdminsAboutLoginJob::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });

        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertCount(0, $user->tokens);
    }

    #[Test]
    public function test_login_fails_with_non_existent_email(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'any-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[Test]
    public function test_login_fails_with_invalid_data_format(): void
    {
        $this->postJson('/api/login', [
            'email' => 'not-an-email-format',
            'password' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
