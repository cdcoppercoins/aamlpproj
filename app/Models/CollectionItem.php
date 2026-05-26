<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    public const CONDITIONS = [
        'MT' => 'Mint (MT)',
        'EX' => 'Excellent (EX)',
        'VG' => 'Very Good (VG)',
        'G' => 'Good (G)',
        'FR' => 'Fair (FR)',
        'PO' => 'Poor (PO)',
    ];

    protected $fillable = [
        'user_id',
        'plate_id',
        'quantity',
        'condition',
        'acquired_date',
        'price_paid',
        'storage_location',
        'notes',
        'is_wanted',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'acquired_date' => 'date',
            'price_paid' => 'decimal:2',
            'is_wanted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plate(): BelongsTo
    {
        return $this->belongsTo(Plate::class);
    }

    public function conditionLabel(): ?string
    {
        if ($this->condition === null || $this->condition === '') {
            return null;
        }

        return self::CONDITIONS[$this->condition] ?? $this->condition;
    }
}
