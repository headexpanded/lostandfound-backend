<?php

namespace Tests\Unit;

use App\Models\LostItem;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_can_list_messages(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        Message::factory()->count(3)->create([
            'from_user_id' => $otherUser->id,
            'to_user_id' => $user->id,
            'lost_item_id' => $lostItem->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'message', 'read', 'created_at', 'updated_at',
                        'from_user', 'to_user', 'lost_item'
                    ]
                ]
            ]);
    }

    public function test_can_create_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $messageData = [
            'to_user_id' => $otherUser->id,
            'lost_item_id' => $lostItem->id,
            'message' => 'I think I found your item!',
        ];

        $response = $this->postJson('/api/messages', $messageData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'message', 'read', 'created_at', 'updated_at',
                    'from_user', 'to_user', 'lost_item'
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'from_user_id' => $user->id,
            'to_user_id' => $otherUser->id,
            'lost_item_id' => $lostItem->id,
            'message' => 'I think I found your item!',
        ]);
    }

    public function test_can_show_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        $message = Message::factory()->create([
            'from_user_id' => $otherUser->id,
            'to_user_id' => $user->id,
            'lost_item_id' => $lostItem->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/messages/{$message->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'message', 'read', 'created_at', 'updated_at',
                    'from_user', 'to_user', 'lost_item'
                ]
            ]);
    }

    public function test_can_update_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        $message = Message::factory()->create([
            'from_user_id' => $user->id,
            'to_user_id' => $otherUser->id,
            'lost_item_id' => $lostItem->id,
        ]);

        Sanctum::actingAs($user);

        $updateData = [
            'message' => 'Updated message content',
        ];

        $response = $this->putJson("/api/messages/{$message->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'message', 'read', 'created_at', 'updated_at',
                    'from_user', 'to_user', 'lost_item'
                ]
            ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Updated message content',
        ]);
    }

    public function test_can_delete_message(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        $message = Message::factory()->create([
            'from_user_id' => $user->id,
            'to_user_id' => $otherUser->id,
            'lost_item_id' => $lostItem->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/messages/{$message->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Message deleted successfully']);

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_can_get_my_messages(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        Message::factory()->count(2)->create([
            'from_user_id' => $otherUser->id,
            'to_user_id' => $user->id,
            'lost_item_id' => $lostItem->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/my-messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'message', 'read', 'created_at', 'updated_at',
                        'from_user', 'lost_item'
                    ]
                ]
            ]);
    }

    public function test_can_mark_message_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $otherUser->id]);

        $message = Message::factory()->create([
            'from_user_id' => $otherUser->id,
            'to_user_id' => $user->id,
            'lost_item_id' => $lostItem->id,
            'read' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Message marked as read']);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'read' => true,
        ]);
    }
}
