<?php

namespace App\Support;

class PlateCsvColumns
{
    /** @var list<string> */
    public const HEADERS = [
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

    /**
     * @param  array{set_code?: string, set_name?: string, company?: string, year?: string|int|null}  $prefill
     * @return list<list<string>>
     */
    public static function exampleRows(array $prefill = []): array
    {
        $setCode = trim((string) ($prefill['set_code'] ?? 'newset'));
        $setName = trim((string) ($prefill['set_name'] ?? 'New Set Name'));
        $company = trim((string) ($prefill['company'] ?? 'Issuer name'));
        $year = trim((string) ($prefill['year'] ?? '1953'));

        if ($setCode === '') {
            $setCode = 'newset';
        }
        if ($setName === '') {
            $setName = 'New Set Name';
        }

        return [
            self::row([
                'set_code' => $setCode,
                'set_name' => $setName,
                'cat_ref' => 'REF-001',
                'company' => $company,
                'image_base' => 'AL',
                'image_ext' => 'jpg',
                'has_back_image' => '1',
                'jurisdiction' => 'Alabama',
                'jurisdiction_type' => 'us_state',
                'year' => $year,
                'serial_number' => '123ABC',
                'width_inches' => '1.5',
                'height_inches' => '2.25',
                'value_mt' => '3.50',
                'value_ex' => '2.00',
                'value_vg' => '1.00',
                'value_g' => '0.75',
                'value_fr' => '0.50',
                'value_po' => '0.25',
                'variety_key' => 'base',
                'state_embossed' => '1',
                'sort_order' => '0',
            ]),
            self::row([
                'set_code' => $setCode,
                'set_name' => $setName,
                'cat_ref' => 'REF-002',
                'company' => $company,
                'image_base' => 'ON',
                'image_ext' => 'jpg',
                'has_back_image' => '0',
                'jurisdiction' => 'Ontario',
                'jurisdiction_type' => 'ca_province',
                'year' => $year,
                'serial_number' => '456XYZ',
                'variety_key' => 'base',
                'sort_order' => '1',
            ]),
            self::row([
                'set_code' => $setCode,
                'set_name' => $setName,
                'company' => $company,
                'year' => $year,
                'jurisdiction' => 'No photo example',
                'jurisdiction_type' => 'us_state',
                'variety_key' => 'base',
                'notes' => 'Leave image_base blank for no photo',
                'sort_order' => '2',
            ]),
        ];
    }

    /**
     * @param  array<string, string|null>  $values
     * @return list<string>
     */
    private static function row(array $values): array
    {
        $row = [];
        foreach (self::HEADERS as $header) {
            $row[] = (string) ($values[$header] ?? '');
        }

        return $row;
    }
}
