<?php

namespace App\Http\Controllers;

use App\Models\CollectionOwnedItem;
use App\Models\MemberMessage;
use App\Models\MemberMessageThread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberMessageController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        $count = MemberMessage::query()
            ->whereHas('thread', function ($query) {
                $query->where('participant_low_id', Auth::id())
                    ->orWhere('participant_high_id', Auth::id());
            })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function threads(): JsonResponse
    {
        $userId = Auth::id();

        $threads = MemberMessageThread::query()
            ->where(function ($query) use ($userId) {
                $query->where('participant_low_id', $userId)
                    ->orWhere('participant_high_id', $userId);
            })
            ->with([
                'ownedItem.collectionItem.plate',
                'ownedItem.collectionItem.user',
                'messages' => fn ($query) => $query->latest()->limit(1),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'threads' => $threads->map(fn (MemberMessageThread $thread) => $this->threadSummary($thread, $userId))->values(),
        ]);
    }

    public function show(MemberMessageThread $thread): JsonResponse
    {
        $this->authorizeThread($thread);

        MemberMessage::query()
            ->where('thread_id', $thread->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $thread->load([
            'ownedItem.collectionItem.plate',
            'ownedItem.collectionItem.user',
            'messages.sender',
        ]);

        return response()->json([
            'thread' => $this->threadSummary($thread, Auth::id()),
            'messages' => $thread->messages->sortBy('created_at')->values()->map(fn (MemberMessage $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_username' => $message->sender->username,
                'is_mine' => $message->sender_id === Auth::id(),
                'created_at' => $message->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owned_item_id' => ['required_without:thread_id', 'integer', 'exists:collection_owned_items,id'],
            'thread_id' => ['required_without:owned_item_id', 'integer', 'exists:member_message_threads,id'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $body = trim($validated['body']);
        if ($body === '') {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        if (! empty($validated['thread_id'])) {
            $thread = MemberMessageThread::query()->findOrFail($validated['thread_id']);
            $this->authorizeThread($thread);
        } else {
            $thread = $this->resolveThreadForListing((int) $validated['owned_item_id']);
        }

        $message = DB::transaction(function () use ($thread, $body) {
            $message = MemberMessage::create([
                'thread_id' => $thread->id,
                'sender_id' => Auth::id(),
                'body' => $body,
            ]);

            $thread->update(['last_message_at' => now()]);

            return $message;
        });

        return response()->json([
            'thread_id' => $thread->id,
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'is_mine' => true,
                'created_at' => $message->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    private function resolveThreadForListing(int $ownedItemId): MemberMessageThread
    {
        $ownedItem = CollectionOwnedItem::query()
            ->with('collectionItem')
            ->findOrFail($ownedItemId);

        if (! $ownedItem->isListed()) {
            abort(422, 'That item is not listed for sale or trade.');
        }

        $ownerId = (int) $ownedItem->collectionItem->user_id;
        $viewerId = Auth::id();

        if ($ownerId === $viewerId) {
            abort(422, 'You cannot message yourself about your own listing.');
        }

        $existing = MemberMessageThread::findForParticipantsAndItem($viewerId, $ownerId, $ownedItemId);

        if ($existing) {
            return $existing;
        }

        return MemberMessageThread::create(array_merge(
            MemberMessageThread::participantPair($viewerId, $ownerId),
            ['owned_item_id' => $ownedItemId]
        ));
    }

    private function authorizeThread(MemberMessageThread $thread): void
    {
        if (! $thread->includesUser(Auth::id())) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function threadSummary(MemberMessageThread $thread, int $viewerId): array
    {
        $otherId = $thread->otherParticipantId($viewerId);
        $other = User::query()->find($otherId);

        $thread->loadMissing([
            'ownedItem.collectionItem.plate',
            'ownedItem.collectionItem.user',
        ]);

        $plate = $thread->ownedItem?->collectionItem?->plate;
        $lastMessage = $thread->messages->first();

        $unread = MemberMessage::query()
            ->where('thread_id', $thread->id)
            ->where('sender_id', '!=', $viewerId)
            ->whereNull('read_at')
            ->count();

        return [
            'id' => $thread->id,
            'owned_item_id' => $thread->owned_item_id,
            'other_username' => $other?->username,
            'other_name' => $other?->name,
            'listing_label' => $thread->ownedItem?->listingLabel(),
            'plate_summary' => $plate
                ? trim(($plate->set_name ?? '') . ' — ' . strtoupper($plate->jurisdiction ?? 'plate'))
                : null,
            'last_message_preview' => $lastMessage ? mb_substr($lastMessage->body, 0, 120) : null,
            'last_message_at' => $thread->last_message_at?->toIso8601String(),
            'unread_count' => $unread,
        ];
    }
}
