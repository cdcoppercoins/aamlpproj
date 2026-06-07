<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberNewsletterCampaign extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'subject',
        'body_html',
        'preview_text',
        'status',
        'created_by',
        'sent_by',
        'recipient_count',
        'sent_count',
        'failed_count',
        'skipped_count',
        'send_started_at',
        'send_completed_at',
        'test_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'skipped_count' => 'integer',
            'send_started_at' => 'datetime',
            'send_completed_at' => 'datetime',
            'test_sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(MemberNewsletterDelivery::class, 'campaign_id');
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENDING => 'Sending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function pendingDeliveryCount(): int
    {
        return $this->deliveries()->where('status', MemberNewsletterDelivery::STATUS_PENDING)->count();
    }
}
