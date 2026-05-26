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

    public function ownedLineValue(): ?float
    {
        if ($this->is_wanted || $this->quantity <= 0 || ! $this->relationLoaded('plate')) {
            return null;
        }

        $unit = $this->plate->numericCatalogValueForCondition($this->condition);
        if ($unit === null) {
            return null;
        }

        return $unit * $this->quantity;
    }

    public function formattedCatalogValueAtCondition(): string
    {
        if ($this->is_wanted || $this->quantity <= 0 || ! $this->relationLoaded('plate')) {
            return '--';
        }

        if ($this->condition === null || $this->condition === '') {
            return '--';
        }

        return $this->plate->displayCatalogValueForCondition($this->condition);
    }

    public function formattedOwnedLineValue(): string
    {
        $line = $this->ownedLineValue();
        if ($line === null) {
            return '--';
        }

        if ($this->quantity <= 1) {
            return Plate::formatCatalogTotal($line);
        }

        return Plate::formatCatalogTotal($line) . ' (' . $this->quantity . ' × ' . $this->formattedCatalogValueAtCondition() . ')';
    }

    /**
     * @param  iterable<int, self>  $items
     */
    public static function sumOwnedLineValues(iterable $items): ?float
    {
        $total = 0.0;
        $hasValue = false;

        foreach ($items as $item) {
            if (! $item->relationLoaded('plate')) {
                $item->load('plate');
            }

            $line = $item->ownedLineValue();
            if ($line === null) {
                continue;
            }

            $total += $line;
            $hasValue = true;
        }

        return $hasValue ? $total : null;
    }
}
