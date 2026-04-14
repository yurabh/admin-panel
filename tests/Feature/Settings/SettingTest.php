<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        config(['cache.default' => 'array']);
        config(['cache.stores.redis.driver' => 'array']);
    }

    #[Test]
    public function test_store_successfully(): void
    {
        Log::spy();

        $payload = [
            'key' => $key = 'site_name',
            'value' => $value = 'My Super App',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/settings', $payload)
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'key', 'value'],
            ]);

        $settingId = $response->json('data.id');

        $this->assertDatabaseHas('settings', [
            'id' => $settingId,
            'key' => $key,
            'value' => $value,
        ]);

        Log::shouldHaveReceived('debug')
            ->with('Setting created', Mockery::subset(['setting' => $settingId]))
            ->once();
    }

    #[Test]
    public function test_show_successfully_from_source(): void
    {
        $setting = Setting::create([
            'key' => 'theme_color',
            'value' => 'blue',
        ]);

        $this->actingAs($this->admin)
            ->getJson("/api/admin/settings/{$setting->key}")
            ->assertOk()
            ->assertJson([
                'key' => 'theme_color',
                'value' => 'blue',
            ]);
    }

    #[Test]
    public function test_show_returns_404_if_not_found(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/admin/settings/non_existent_key')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found']);
    }

    #[Test]
    public function test_store_validation_fails_for_duplicate_key(): void
    {
        Setting::create([
            'key' => 'unique_key',
            'value' => 'some value',
        ]);

        $this->actingAs($this->admin)
            ->postJson('/api/admin/settings', [
                'key' => 'unique_key',
                'value' => 'some value',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['key']);
    }
}
