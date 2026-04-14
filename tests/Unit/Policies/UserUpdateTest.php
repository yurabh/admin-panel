<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
    }

    #[Test]
    public function an_admin_can_update_any_user(): void
    {
        $admin = $this->getMock();

        $admin->method('__get')->willReturnMap([
            ['role', UserRole::ADMIN],
            ['id', 1],
        ]);

        $someUser = $this->getMock();

        $this->assertTrue($this->policy->update($admin, $someUser));
    }

    #[Test]
    public function a_regular_user_cannot_update_others(): void
    {
        $someUser = $this->getMock();

        $someUser->method('__get')
            ->willReturnCallback(fn ($prop) => match ($prop) {
                'id' => 1,
                'role' => UserRole::USER,
                default => null
            });

        $anotherUser = $this->getMock();

        $anotherUser->method('__get')
            ->with('id')
            ->willReturn(2);

        $this->assertFalse($this->policy->update($someUser, $anotherUser));
    }

    private function getMock(): User
    {
        return $this->createMock(User::class);
    }
}
