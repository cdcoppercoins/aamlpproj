<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberMessageThread extends Model
{
    protected $fillable = [
        'participant_low_id',
        'participant_high_id',
        'owned_item_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function participantLow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_low_id');
    }

    public function participantHigh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_high_id');
    }

    public function ownedItem(): BelongsTo
    {
        return $this->belongsTo(CollectionOwnedItem::class, 'owned_item_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MemberMessage::class, 'thread_id');
    }

    public function includesUser(int $userId): bool
    {
        return $this->participant_low_id === $userId || $this->participant_high_id === $userId;
    }

    public function otherParticipantId(int $userId): int
    {
        return $this->participant_low_id === $userId
            ? $this->participant_high_id
            : $this->participant_low_id;
    }

    public static function participantPair(int $userA, int $userB): array
    {
        return $userA < $userB
            ? ['participant_low_id' => $userA, 'participant_high_id' => $userB]
            : ['participant_low_id' => $userB, 'participant_high_id' => $userA];
    }

    public static function findForParticipantsAndItem(int $userA, int $userB, int $ownedItemId): ?self
    {
        $pair = self::participantPair($userA, $userB);

        return self::query()
            ->where($pair)
            ->where('owned_item_id', $ownedItemId)
            ->first();
    }
}
