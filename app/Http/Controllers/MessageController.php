<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $messages = Message::with(['fromUser', 'toUser', 'lostItem'])
            ->where('to_user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $request->to_user_id,
            'lost_item_id' => $request->lost_item_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => new MessageResource($message->load(['fromUser', 'toUser', 'lostItem']))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message): MessageResource
    {
        // Ensure user can only view messages they sent or received
        if ($message->from_user_id !== auth()->id() && $message->to_user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Mark as read if the current user is the recipient
        if ($message->to_user_id === auth()->id() && !$message->read) {
            $message->update(['read' => true]);
        }

        return new MessageResource($message->load(['fromUser', 'toUser', 'lostItem']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        // Ensure user can only update messages they sent
        if ($message->from_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update($request->validated());

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => new MessageResource($message->load(['fromUser', 'toUser', 'lostItem']))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message): JsonResponse
    {
        // Ensure user can only delete messages they sent or received
        if ($message->from_user_id !== auth()->id() && $message->to_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    /**
     * Get user's messages (inbox).
     */
    public function myMessages(Request $request): AnonymousResourceCollection
    {
        $messages = $request->user()
            ->receivedMessages()
            ->with(['fromUser', 'lostItem'])
            ->latest()
            ->paginate(15);

        return MessageResource::collection($messages);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): JsonResponse
    {
        // Ensure user can only mark messages they received as read
        if ($message->to_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->update(['read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }
}
