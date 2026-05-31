<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionItem extends Model
{
    /** @var list<string> Best to worst */
    public const GRADE_CODES = ['MT', 'EX', 'VG', 'G', 'FR', 'PO'];

    public const GRADES = [
        'MT' => 'Mint (MT)',
        'EX' => 'Excellent (EX)',
        'VG' => 'Very Good (VG)',
        'G' => 'Good (G)',
        'FR' => 'Fair (FR)',
        'PO' => 'Poor (PO)',
    ];

    /** @deprecated Use GRADES */
    public const CONDITIONS = self::GRADES;

    protected $fillable = [
        'user_id',
        'plate_id',
        'notes',
        'is_wanted',
    ];

    protected function casts(): array
    {
        return [
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

    public function ownedItems(): HasMany
    {
        return $this->hasMany(CollectionOwnedItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function ownedItemCount(): int
    {
        if ($this->is_wanted) {
            return 0;
        }

        if ($this->relationLoaded('ownedItems')) {
            return $this->ownedItems->count();
        }

        return $this->ownedItems()->count();
    }

    public function getQuantityAttribute(): int
    {
        return $this->ownedItemCount();
    }

    /**
     * @return list<string|null>
     */
    public function itemGrades(): array
    {
        if ($this->is_wanted) {
            return [];
        }

        if ($this->relationLoaded('ownedItems')) {
            return $this->ownedItems->map(static fn (CollectionOwnedItem $ownedItem) => $ownedItem->normalizedGrade())->all();
        }

        return $this->ownedItems()
            ->pluck('grade')
            ->map(static fn ($grade) => ($grade === '' || $grade === null) ? null : (string) $grade)
            ->all();
    }

    /** @deprecated Use itemGrades() */
    public function copyConditions(): array
    {
        return $this->itemGrades();
    }

    public function conditionLabel(): ?string
    {
        $summary = $this->gradeSummary();

        return $summary !== '' ? $summary : null;
    }

    public function gradeSummary(): string
    {
        return $this->conditionSummary();
    }

    public function conditionSummary(): string
    {
        $grades = array_values(array_filter($this->itemGrades()));
        if ($grades === []) {
            return '';
        }

        $counts = self::sortGradeCounts(array_count_values($grades));
        $parts = [];

        foreach ($counts as $code => $count) {
            $parts[] = $count > 1 ? $count.'×'.$code : $code;
        }

        return implode(', ', $parts);
    }

    public function ownedLineValue(): ?float
    {
        if ($this->is_wanted || ! $this->relationLoaded('plate')) {
            return null;
        }

        if ($this->ownedItemCount() === 0) {
            return null;
        }

        $total = 0.0;
        $hasValue = false;

        foreach ($this->itemGrades() as $grade) {
            if (! $grade) {
                continue;
            }

            $unit = $this->plate->numericCatalogValueForCondition($grade);
            if ($unit === null) {
                continue;
            }

            $total += $unit;
            $hasValue = true;
        }

        return $hasValue ? $total : null;
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<string, int>
     */
    public static function sortGradeCounts(array $counts): array
    {
        $sorted = [];

        foreach (self::GRADE_CODES as $code) {
            if (isset($counts[$code])) {
                $sorted[$code] = $counts[$code];
            }
        }

        return $sorted;
    }

    public function formattedCatalogValueAtCondition(): string
    {
        if ($this->is_wanted || $this->ownedItemCount() === 0 || ! $this->relationLoaded('plate')) {
            return '--';
        }

        $grades = array_values(array_filter($this->itemGrades()));
        if ($grades === []) {
            return '--';
        }

        $unique = array_unique($grades);
        if (count($unique) === 1) {
            return $this->plate->displayCatalogValueForCondition($unique[0]);
        }

        return 'Mixed grades';
    }

    public function formattedOwnedLineValue(): string
    {
        $line = $this->ownedLineValue();
        if ($line === null) {
            return '--';
        }

        $grades = array_values(array_filter($this->itemGrades()));
        if ($grades === []) {
            return '--';
        }

        $counts = self::sortGradeCounts(array_count_values($grades));

        if (count($counts) === 1) {
            $code = array_key_first($counts);
            $unit = $this->plate->displayCatalogValueForCondition($code);
            $count = $counts[$code];

            if ($count <= 1) {
                return Plate::formatCatalogTotal($line);
            }

            return Plate::formatCatalogTotal($line).' ('.$count.' × '.$unit.')';
        }

        return Plate::formatCatalogTotal($line).' ('.$this->gradeSummary().')';
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $rows
     */
    public function syncOwnedItems(?array $rows): void
    {
        $existingSerials = $this->ownedItems()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('serial_number')
            ->all();

        DB::transaction(function () use ($rows, $existingSerials): void {
            $this->ownedItems()->delete();

            if ($this->is_wanted || ! is_array($rows)) {
                return;
            }

            foreach (array_values($rows) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $payload = self::normalizeOwnedItemPayload($row);
                if (! self::ownedItemPayloadHasContent($payload)) {
                    continue;
                }

                unset($payload['serial_number']);
                if (! empty($existingSerials[$index])) {
                    $payload['serial_number'] = $existingSerials[$index];
                }

                $this->ownedItems()->create(array_merge($payload, [
                    'sort_order' => $index,
                ]));
            }
        });
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function normalizeOwnedItemPayload(array $row): array
    {
        $grade = $row['grade'] ?? null;
        if ($grade !== null && ! in_array($grade, self::GRADE_CODES, true)) {
            $grade = null;
        }

        return [
            'grade' => $grade,
            'acquired_date' => self::nullableString($row['acquired_date'] ?? null, 10),
            'price_paid' => isset($row['price_paid']) && $row['price_paid'] !== '' ? $row['price_paid'] : null,
            'storage_location' => self::nullableString($row['storage_location'] ?? null, 128),
            'notes' => self::nullableString($row['notes'] ?? null, 5000),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function ownedItemPayloadHasContent(array $payload): bool
    {
        foreach (['grade', 'acquired_date', 'price_paid', 'storage_location', 'notes'] as $field) {
            if (($payload[$field] ?? null) !== null && ($payload[$field] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    private static function nullableString(mixed $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $maxLength);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ownedItemsFormRows(): array
    {
        return $this->ownedItems->map(static function (CollectionOwnedItem $ownedItem) {
            return [
                'grade' => $ownedItem->grade,
                'serial_number' => $ownedItem->serial_number,
                'acquired_date' => $ownedItem->acquired_date?->format('Y-m-d'),
                'price_paid' => $ownedItem->price_paid,
                'storage_location' => $ownedItem->storage_location,
                'notes' => $ownedItem->notes,
            ];
        })->all();
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

            if (! $item->relationLoaded('ownedItems')) {
                $item->load('ownedItems');
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
