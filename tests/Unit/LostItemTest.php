<?php

namespace Tests\Unit;

use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LostItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_can_list_lost_items(): void
    {
        $user = User::factory()->create();
        LostItem::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/lost-items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'description', 'location', 'backstory',
                        'keywords', 'status', 'fee_paid', 'lost_date',
                        'created_at', 'updated_at', 'user'
                    ]
                ]
            ]);
    }

    public function test_can_create_lost_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lostItemData = [
            'title' => 'Lost Car Keys',
            'description' => 'Silver car keys with remote',
            'location' => 'Main Street',
            'backstory' => 'Lost while walking the dog',
            'keywords' => ['keys', 'car', 'silver'],
            'fee_paid' => 5.00,
            'lost_date' => '2024-01-15',
        ];

        $response = $this->postJson('/api/lost-items', $lostItemData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'title', 'description', 'location', 'backstory',
                    'keywords', 'status', 'fee_paid', 'lost_date',
                    'created_at', 'updated_at', 'user'
                ]
            ]);

        $this->assertDatabaseHas('lost_items', [
            'title' => 'Lost Car Keys',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_show_lost_item(): void
    {
        $user = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/lost-items/{$lostItem->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'location', 'backstory',
                    'keywords', 'status', 'fee_paid', 'lost_date',
                    'created_at', 'updated_at', 'user'
                ]
            ]);
    }

    public function test_can_update_lost_item(): void
    {
        $user = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/lost-items/{$lostItem->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'title', 'description', 'location', 'backstory',
                    'keywords', 'status', 'fee_paid', 'lost_date',
                    'created_at', 'updated_at', 'user'
                ]
            ]);

        $this->assertDatabaseHas('lost_items', [
            'id' => $lostItem->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_lost_item(): void
    {
        $user = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/lost-items/{$lostItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Lost item deleted successfully']);

        $this->assertDatabaseMissing('lost_items', ['id' => $lostItem->id]);
    }

    public function test_can_search_lost_items(): void
    {
        $user = User::factory()->create();
        LostItem::factory()->create([
            'user_id' => $user->id,
            'location' => 'Main Street',
            'keywords' => ['bicycle', 'blue'],
        ]);

        $response = $this->getJson('/api/lost-items/search?location=Main&keywords=bicycle');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'description', 'location', 'backstory',
                        'keywords', 'status', 'fee_paid', 'lost_date',
                        'created_at', 'updated_at', 'user'
                    ]
                ]
            ]);
    }

    public function test_can_get_my_lost_items(): void
    {
        $user = User::factory()->create();
        LostItem::factory()->count(2)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/my-lost-items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'description', 'location', 'backstory',
                        'keywords', 'status', 'fee_paid', 'lost_date',
                        'created_at', 'updated_at', 'user'
                    ]
                ]
            ]);
    }
}
