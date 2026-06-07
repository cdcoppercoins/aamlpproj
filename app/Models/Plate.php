<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Plate extends Model
{
    private const CONDITION_CODES = ['MT', 'EX', 'VG', 'G', 'FR', 'PO'];

    protected $fillable = [
        'set_code',
        'set_name',
        'cat_ref',
        'company',
        'image_base',
        'image_ext',
        'has_back_image',
        'jurisdiction',
        'jurisdiction_type',
        'year',
        'serial_number',
        'width_inches',
        'height_inches',
        'value_mt',
        'value_ex',
        'value_vg',
        'value_g',
        'value_fr',
        'value_po',
        'variety_key',
        'variety_notes',
        'state_embossed',
        'legend_embossed',
        'notes',
        'sort_order',
    ];

    public function frontImageUrl(): ?string
    {
        if (empty($this->image_base) || empty($this->image_ext)) {
            return null;
        }

        return asset('plates/' . $this->set_code . '/' . $this->image_base . '_a.' . $this->image_ext);
    }

    public function backImageUrl(): ?string
    {
        if (empty($this->image_base) || empty($this->image_ext) || !$this->has_back_image) {
            return null;
        }

        return asset('plates/' . $this->set_code . '/' . $this->image_base . '_b.' . $this->image_ext);
    }

    public function formattedSize(): ?string
    {
        $width = self::formatInches($this->width_inches);
        $height = self::formatInches($this->height_inches);

        if ($width === null || $height === null) {
            return null;
        }

        return "{$width} x {$height} in.";
    }

    public function displayValue(string $field): string
    {
        $value = trim((string) ($this->{$field} ?? ''));

        if ($value === '') {
            return '--';
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            return '$' . rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
        }

        if (preg_match('/^\d/', $value) && !str_starts_with($value, '$')) {
            return '$' . $value;
        }

        return $value;
    }

    public static function conditionValueField(?string $condition): ?string
    {
        return match ($condition) {
            'MT' => 'value_mt',
            'EX' => 'value_ex',
            'VG' => 'value_vg',
            'G' => 'value_g',
            'FR' => 'value_fr',
            'PO' => 'value_po',
            default => null,
        };
    }

    public function catalogValueForCondition(?string $condition): ?string
    {
        $field = self::conditionValueField($condition);
        if ($field === null) {
            return null;
        }

        $value = trim((string) ($this->{$field} ?? ''));

        return $value !== '' ? $value : null;
    }

    public function displayCatalogValueForCondition(?string $condition): string
    {
        $field = self::conditionValueField($condition);
        if ($field === null) {
            return '--';
        }

        return $this->displayValue($field);
    }

    public function numericCatalogValueForCondition(?string $condition): ?float
    {
        $raw = $this->catalogValueForCondition($condition);
        if ($raw === null) {
            return null;
        }

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        if (preg_match('/^([\d.]+)\s*-\s*([\d.]+)/', $raw, $matches)) {
            return ((float) $matches[1] + (float) $matches[2]) / 2;
        }

        $cleaned = preg_replace('/[^\d.-]/', '', $raw);
        if ($cleaned !== '' && is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return null;
    }

    /**
     * @return array<string, string|null>
     */
    public function catalogDisplayValuesByCondition(): array
    {
        $values = [];
        foreach (self::CONDITION_CODES as $code) {
            $values[$code] = $this->catalogValueForCondition($code) !== null
                ? $this->displayCatalogValueForCondition($code)
                : null;
        }

        return $values;
    }

    /**
     * @return array<string, float|null>
     */
    public function catalogNumericValuesByCondition(): array
    {
        $values = [];
        foreach (self::CONDITION_CODES as $code) {
            $values[$code] = $this->numericCatalogValueForCondition($code);
        }

        return $values;
    }

    public static function formatCatalogTotal(?float $total): string
    {
        if ($total === null) {
            return '--';
        }

        return '$' . number_format($total, 2, '.', ',');
    }

    /**
     * Plate label from catalog notes for the 1968 Maple Leaf set (e.g. "Plate #01").
     */
    public function catalogPlateNotesLine(): ?string
    {
        $setCode = strtolower((string) ($this->set_code ?? ''));
        $isMapleLeaf1968 = in_array($setCode, ['m68', 'm68ml'], true)
            || str_contains((string) ($this->set_name ?? ''), '1968 Maple Leaf');

        if (! $isMapleLeaf1968) {
            return null;
        }

        $notes = trim((string) ($this->notes ?? ''));

        return $notes !== '' ? $notes : null;
    }

    /**
     * Card/checklist number stored in catalog notes for a few sets (e.g. 1950 Stop & Go).
     */
    public function catalogCardNumber(): ?string
    {
        $notes = trim((string) ($this->notes ?? ''));
        if ($notes === '' || ! preg_match('/^\d+[a-z]?$/i', $notes)) {
            return null;
        }

        return $notes;
    }

    public function scopeOrderedForCatalog(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('variety_key')
            ->orderBy('id');
    }

    /**
     * @param  Collection<int, Plate>  $plates
     */
    public static function platesUseCatalogCardNumbers(Collection $plates): bool
    {
        $withNotes = $plates
            ->filter(fn (Plate $plate) => trim((string) ($plate->notes ?? '')) !== '')
            ->count();

        if ($withNotes < 5) {
            return false;
        }

        $cardLike = $plates
            ->filter(fn (Plate $plate) => $plate->catalogCardNumber() !== null)
            ->count();

        return $cardLike >= (int) ceil($withNotes * 0.8);
    }

    public static function formatInches(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $inches = (float) $value;
        $whole = (int) floor($inches + 0.0001);
        $sixteenths = (int) round(($inches - $whole) * 16);

        if ($sixteenths === 16) {
            $whole++;
            $sixteenths = 0;
        }

        if ($sixteenths === 0) {
            return (string) $whole;
        }

        $fractions = [
            1 => '1/16', 2 => '1/8', 3 => '3/16', 4 => '1/4', 5 => '5/16',
            6 => '3/8', 7 => '7/16', 8 => '1/2', 9 => '9/16', 10 => '5/8',
            11 => '11/16', 12 => '3/4', 13 => '13/16', 14 => '7/8', 15 => '15/16',
        ];

        $fraction = $fractions[$sixteenths] ?? '';

        if ($whole === 0) {
            return $fraction;
        }

        return trim($whole . ' ' . $fraction);
    }
}
