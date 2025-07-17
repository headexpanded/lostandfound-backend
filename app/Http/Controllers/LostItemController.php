<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLostItemRequest;
use App\Http\Requests\UpdateLostItemRequest;
use App\Http\Resources\LostItemResource;
use App\Models\LostItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LostItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $lostItems = LostItem::with('user')
            ->active()
            ->latest()
            ->paginate(15);

        return LostItemResource::collection($lostItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLostItemRequest $request): JsonResponse
    {
        $lostItem = $request->user()->lostItems()->create($request->validated());

        return response()->json([
            'message' => 'Lost item created successfully',
            'data' => new LostItemResource($lostItem->load('user'))
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LostItem $lostItem): LostItemResource
    {
        return new LostItemResource($lostItem->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLostItemRequest $request, LostItem $lostItem): JsonResponse
    {
        // Ensure user can only update their own lost items
        if ($lostItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lostItem->update($request->validated());

        return response()->json([
            'message' => 'Lost item updated successfully',
            'data' => new LostItemResource($lostItem->load('user'))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, LostItem $lostItem): JsonResponse
    {
        // Ensure user can only delete their own lost items
        if ($lostItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lostItem->delete();

        return response()->json(['message' => 'Lost item deleted successfully']);
    }

    /**
     * Search lost items by various criteria.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $query = LostItem::with('user')->active();

        // Search by location
        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Search by keywords
        if ($request->filled('keywords')) {
            $keywords = explode(',', $request->keywords);
            foreach ($keywords as $keyword) {
                $query->byKeywords(trim($keyword));
            }
        }

        // Search by date range
        if ($request->filled('date_from')) {
            $query->where('lost_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('lost_date', '<=', $request->date_to);
        }

        // Full-text search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('backstory', 'like', "%{$searchTerm}%");
            });
        }

        $lostItems = $query->latest()->paginate(15);

        return LostItemResource::collection($lostItems);
    }

    /**
     * Get user's own lost items.
     */
    public function myItems(Request $request): AnonymousResourceCollection
    {
        $lostItems = $request->user()
            ->lostItems()
            ->latest()
            ->paginate(15);

        return LostItemResource::collection($lostItems);
    }
}
