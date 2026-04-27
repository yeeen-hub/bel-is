<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeCategoryAndAttractionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── 1. Clear existing data ────────────────────────────────────────────
        DB::table('fee_categories')->delete();
        DB::table('barangay_attractions')->delete();

        $this->command->info('Cleared existing fee categories and attractions.');

        // ── 2. Fee Categories ─────────────────────────────────────────────────
        $categories = [
            [
                'category'   => 'Child',
                'age_range'  => '0 – 12 years old',
                'fee'        => 20,
                'updated_by' => 'System Admin',
            ],
            [
                'category'   => 'Student/PWD',
                'age_range'  => '13 – 17 / with ID',
                'fee'        => 50,
                'updated_by' => 'System Admin',
            ],
            [
                'category'   => 'Adult',
                'age_range'  => '18 – 59 years old',
                'fee'        => 100,
                'updated_by' => 'System Admin',
            ],
            [
                'category'   => 'Senior Citizen',
                'age_range'  => '60 years old & above',
                'fee'        => 50,
                'updated_by' => 'System Admin',
            ],
            [
                'category'   => 'Foreign National',
                'age_range'  => 'Any age',
                'fee'        => 200,
                'updated_by' => 'System Admin',
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('fee_categories')->insert(array_merge($cat, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $this->command->info('✓ ' . count($categories) . ' fee categories seeded.');

        // ── 3. Sitios — ensure they exist (upsert by name) ───────────────────
        $sitios = [
            'Hinugtan', 'Nasog', 'Langka', 'Kalamangyan',
            'Centro', 'Seaside', 'Mountainside',
        ];

        foreach ($sitios as $sitioName) {
            $existing = DB::table('sitios')->where('name', $sitioName)->first();
            if (!$existing) {
                DB::table('sitios')->insert([
                    'name'       => $sitioName,
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('✓ Sitios verified/created.');

        // ── 4. Fetch sitio IDs ────────────────────────────────────────────────
        $sitioMap = DB::table('sitios')
            ->whereIn('name', $sitios)
            ->get()
            ->keyBy('name')
            ->map(fn($s) => $s->id);

        // ── 5. Barangay Attractions ───────────────────────────────────────────
        $attractions = [
            // Beaches
            [
                'name'      => 'Hinugtan White Beach',
                'type'      => 'Beach',
                'sitio'     => 'Hinugtan',
                'is_active' => 1,
            ],
            [
                'name'      => 'Nasog White Beach',
                'type'      => 'Beach',
                'sitio'     => 'Nasog',
                'is_active' => 1,
            ],
            [
                'name'      => 'Langka White Beach',
                'type'      => 'Beach',
                'sitio'     => 'Langka',
                'is_active' => 1,
            ],
            [
                'name'      => 'Kalamangyan Beach',
                'type'      => 'Beach',
                'sitio'     => 'Kalamangyan',
                'is_active' => 1,
            ],
            [
                'name'      => 'Proper White Beach',
                'type'      => 'Beach',
                'sitio'     => 'Centro',
                'is_active' => 1,
            ],
            [
                'name'      => 'Bel-is Cove Beach Resort',
                'type'      => 'Resort',
                'sitio'     => 'Hinugtan',
                'is_active' => 1,
            ],
            [
                'name'      => 'Paradiso Bel-is',
                'type'      => 'Resort',
                'sitio'     => 'Hinugtan',
                'is_active' => 1,
            ],
            // Springs & Water Features
            [
                'name'      => 'Kalamangyan Spring Beach',
                'type'      => 'Spring',
                'sitio'     => 'Kalamangyan',
                'is_active' => 1,
            ],
            [
                'name'      => 'Butong Spring',
                'type'      => 'Spring',
                'sitio'     => 'Seaside',
                'is_active' => 1,
            ],
            // Natural & Marine
            [
                'name'      => 'Liugan Fish Sanctuary',
                'type'      => 'Marine Sanctuary',
                'sitio'     => 'Seaside',
                'is_active' => 1,
            ],
            [
                'name'      => 'Bugtong Bato',
                'type'      => 'Rock Formation',
                'sitio'     => 'Mountainside',
                'is_active' => 1,
            ],
        ];

        foreach ($attractions as $attr) {
            $sitioId = $sitioMap[$attr['sitio']] ?? null;
            DB::table('barangay_attractions')->insert([
                'name'        => $attr['name'],
                'type'        => $attr['type'],
                'sitio_id'    => $sitioId,
                'is_active'   => $attr['is_active'],
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $this->command->info('✓ ' . count($attractions) . ' attractions seeded.');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('  Fee Categories : ' . DB::table('fee_categories')->count());
        $this->command->info('  Attractions    : ' . DB::table('barangay_attractions')->count());
        $this->command->info('');
        $this->command->info('Done! You can now run VisitorSeeder to generate sample visitor data.');
    }
}