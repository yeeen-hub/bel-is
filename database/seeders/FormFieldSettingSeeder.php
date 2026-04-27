<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormFieldSettingSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // Always required — cannot be toggled off (enforced in controller)
            ['field_key' => 'surname',      'label' => 'Surname',                        'is_required' => true,  'is_visible' => true,  'sort_order' => 1],
            ['field_key' => 'first_name',   'label' => 'First Name',                     'is_required' => true,  'is_visible' => true,  'sort_order' => 2],
            ['field_key' => 'middle_name',  'label' => 'Middle Name',                    'is_required' => false, 'is_visible' => true,  'sort_order' => 3],
            ['field_key' => 'address',      'label' => 'Address (Town/City + Country)',  'is_required' => true,  'is_visible' => true,  'sort_order' => 4],
            ['field_key' => 'sex',          'label' => 'Sex',                            'is_required' => true,  'is_visible' => true,  'sort_order' => 5],
            ['field_key' => 'age',          'label' => 'Age',                            'is_required' => true,  'is_visible' => true,  'sort_order' => 6],
            ['field_key' => 'nationality',  'label' => 'Nationality',                    'is_required' => true,  'is_visible' => true,  'sort_order' => 7],
            // Optional by default — can be toggled required
            ['field_key' => 'contact_number', 'label' => 'Contact Number',              'is_required' => false, 'is_visible' => true,  'sort_order' => 8],
            ['field_key' => 'remarks',      'label' => 'Remarks',                        'is_required' => false, 'is_visible' => true,  'sort_order' => 9],
            ['field_key' => 'accommodation','label' => 'Accommodation',                  'is_required' => false, 'is_visible' => true,  'sort_order' => 10],
            ['field_key' => 'purpose',      'label' => 'Purpose of Visit',               'is_required' => true,  'is_visible' => true,  'sort_order' => 11],
        ];

        foreach ($fields as $field) {
            DB::table('form_field_settings')->updateOrInsert(
                ['field_key' => $field['field_key']],
                array_merge($field, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}