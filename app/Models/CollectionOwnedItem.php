<?php

namespace App\Models;

use App\Support\CollectionSerialAssigner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionOwnedItem extends Model
{
    protected $table = 'collection_owned_items';

    protected $fillable = [
        'collection_item_id',
        'grade',
        'serial_number',
        'acquired_date',
        'price_paid',
        'storage_location',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'acquired_date' => 'date',
            'price_paid' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            $serial = $item->serial_number;

            if ($serial !== null && $serial !== '') {
                CollectionSerialAssigner::assertUnique($serial);

                return;
            }

            $item->loadMissing('collectionItem.plate');
            $plateYear = $item->collectionItem?->plate?->year;
            $year = $plateYear !== null ? (int) $plateYear : null;
            $item->serial_number = CollectionSerialAssigner::nextSerialForYear($year);
        });

        static::updating(function (self $item): void {
            if ($item->isDirty('serial_number')) {
                $item->serial_number = $item->getOriginal('serial_number');
            }
        });
    }

    public function collectionItem(): BelongsTo
    {
        return $this->belongsTo(CollectionItem::class);
    }

    public function normalizedGrade(): ?string
    {
        $grade = $this->grade;

        return ($grade === '' || $grade === null) ? null : (string) $grade;
    }
}
