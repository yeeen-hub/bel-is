<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        // ── Fetch real data from DB ───────────────────────────────────────────
        $feeCategories = DB::table('fee_categories')->get();
        $attractions   = DB::table('barangay_attractions')->where('is_active', 1)->get();
        $adminId       = DB::table('users')->where('email', 'admin@belischeckpoint.ph')->value('id') ?? 1;

        if ($feeCategories->isEmpty()) {
            $this->command->error('No fee categories found. Please add them first.');
            return;
        }
        if ($attractions->isEmpty()) {
            $this->command->error('No attractions found. Please add them first.');
            return;
        }

        $this->command->info('Fee categories: ' . $feeCategories->pluck('category')->implode(', '));
        $this->command->info('Attractions: ' . $attractions->pluck('name')->implode(', '));

        // Build category pools from actual DB data
        // We group them into age-derivable and manual-only
        $numericCats = []; // categories with numeric age ranges (auto-derived)
        $manualCats  = []; // categories with non-numeric ranges (Foreign National, etc.)

        foreach ($feeCategories as $cat) {
            $r = strtolower($cat->age_range ?? '');
            if (preg_match('/\d/', $r)) {
                $numericCats[] = $cat;
            } else {
                $manualCats[] = $cat;
            }
        }

        // ── Name pools ────────────────────────────────────────────────────────
        $firstNames = [
            'Maria', 'Jose', 'Juan', 'Ana', 'Carlo', 'Liza', 'Marco', 'Rosa',
            'Miguel', 'Luisa', 'Ramon', 'Elena', 'Felix', 'Clara', 'Diego',
            'Nora', 'Emmanuel', 'Paz', 'Roberto', 'Carla', 'Eduardo', 'Ines',
            'Antonio', 'Bella', 'Fernando', 'Alma', 'Gabriel', 'Rita', 'Samuel',
            'Teresa', 'Vincent', 'Lorna', 'Patrick', 'Grace', 'Dennis', 'Gina',
            'James', 'Rowena', 'Mark', 'Sheila', 'Kevin', 'Maricel', 'Ryan',
            'Jennilyn', 'Christian', 'Joanna', 'Nathan', 'Cristina', 'Daniel',
            'Melissa', 'Rodel', 'Marites', 'Noel', 'Cynthia', 'Arnold', 'Evelyn',
        ];
        $lastNames = [
            'Santos', 'Reyes', 'Cruz', 'Garcia', 'Ramos', 'Flores', 'Mendoza',
            'Torres', 'Villanueva', 'Aquino', 'Bautista', 'Dela Cruz', 'Lim',
            'Gonzales', 'Navarro', 'Castillo', 'Morales', 'Aguilar', 'Pascual',
            'Valencia', 'Salazar', 'Ibarra', 'Ocampo', 'Manalo', 'Fernandez',
            'Rivera', 'Diaz', 'Lopez', 'Hernandez', 'Jimenez', 'Robles',
            'Sanchez', 'Ramirez', 'Mercado', 'Cabrera', 'Soriano', 'Abad',
            'Panganiban', 'Sta. Maria', 'Dela Torre', 'Tanieza', 'Panagsagan',
            'Cabangon', 'Flores', 'Villasanta', 'Lihaylihay', 'Bernardino',
        ];
        $middleNames = [
            'Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Lopez', 'Torres',
            'Gonzales', 'Flores', 'Ramos', 'Bautista', null, null, null, null,
        ];

        // ── Origin pools ──────────────────────────────────────────────────────
        // Weighted: ~70% Local PH, ~15% Aklanon, ~10% Foreign, ~5% OFW
        $origins = [
            // Local Philippine cities
            ['town' => 'Kalibo',        'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Kalibo',        'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Manila',        'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Manila',        'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Makati',        'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Cebu City',     'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Iloilo City',   'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Iloilo City',   'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Davao City',    'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Roxas City',    'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Boracay',       'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Boracay',       'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Quezon City',   'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Pasig',         'country' => 'Philippines', 'nat' => 'Local'],
            ['town' => 'Cagayan de Oro','country' => 'Philippines', 'nat' => 'Local'],
            // Aklanon
            ['town' => 'Buruanga',      'country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'Buruanga',      'country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'New Washington','country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'Batan',         'country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'Malinao',       'country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'Nabas',         'country' => 'Philippines', 'nat' => 'Aklanon'],
            ['town' => 'Lezo',          'country' => 'Philippines', 'nat' => 'Aklanon'],
            // Foreign
            ['town' => 'Tokyo',         'country' => 'Japan',        'nat' => 'Foreign'],
            ['town' => 'Seoul',         'country' => 'South Korea',  'nat' => 'Foreign'],
            ['town' => 'Singapore',     'country' => 'Singapore',    'nat' => 'Foreign'],
            ['town' => 'Sydney',        'country' => 'Australia',    'nat' => 'Foreign'],
            ['town' => 'London',        'country' => 'United Kingdom','nat'=> 'Foreign'],
            ['town' => 'New York',      'country' => 'USA',          'nat' => 'Foreign'],
            ['town' => 'Berlin',        'country' => 'Germany',      'nat' => 'Foreign'],
            ['town' => 'Paris',         'country' => 'France',       'nat' => 'Foreign'],
            // OFW
            ['town' => 'Dubai',         'country' => 'UAE',          'nat' => 'OFW'],
            ['town' => 'Riyadh',        'country' => 'Saudi Arabia', 'nat' => 'OFW'],
            ['town' => 'Hong Kong',     'country' => 'Hong Kong',    'nat' => 'OFW'],
        ];

        $purposes    = ['Tourism', 'Tourism', 'Tourism', 'Tourism', 'Tourism',
                        'Research', 'Event', 'Official Visit'];
        $feeStatuses = ['Collected', 'Collected', 'Collected', 'Collected',
                        'Collected', 'Collected', 'Waived', 'Pending'];
        $waivers     = ['Resident', 'Official Business', 'PWD'];
        $sexes       = ['M', 'M', 'F', 'F', 'M', 'F'];

        // ── Helper: derive category from age using real DB ranges ─────────────
        $deriveCategory = function (int $age) use ($numericCats) {
            foreach ($numericCats as $cat) {
                $r = strtolower($cat->age_range ?? '');
                if (str_contains($r, 'above') || str_contains($r, 'abov')) {
                    preg_match('/(\d+)/', $r, $m);
                    if (isset($m[1]) && $age >= (int) $m[1]) return $cat;
                }
                if (str_contains($r, 'below')) {
                    preg_match('/(\d+)/', $r, $m);
                    if (isset($m[1]) && $age <= (int) $m[1]) return $cat;
                }
                if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', $r, $m)) {
                    if ($age >= (int) $m[1] && $age <= (int) $m[2]) return $cat;
                }
            }
            // fallback to first numeric category
            return $numericCats[0] ?? $feeCategories->first();
        };

        // ── Helper: pick a category with realistic age ────────────────────────
        $pickAgeAndCategory = function () use ($numericCats, $manualCats, $deriveCategory, $origins) {
            // 80% numeric (auto-derivable), 20% manual (Foreign, etc.) if any exist
            if (!empty($manualCats) && rand(1, 100) <= 20) {
                $cat = $manualCats[array_rand($manualCats)];
                return ['age' => null, 'cat' => $cat];
            }
            // Pick a random age and derive category
            $age = rand(5, 85);
            $cat = $deriveCategory($age);
            return ['age' => $age, 'cat' => $cat];
        };

        $attractionIds = $attractions->pluck('id')->toArray();

        // Spread visits over last 90 days, weighted toward recent
        // Always start OR numbering from 1 — assumes table was cleared before seeding
        $orCounter = 1;
        $dailyCounts = [];

        $this->command->info('Seeding 100 visitors...');

        for ($i = 1; $i <= 100; $i++) {
            $firstName  = $firstNames[array_rand($firstNames)];
            $lastName   = $lastNames[array_rand($lastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $origin     = $origins[array_rand($origins)];
            $sex        = $sexes[array_rand($sexes)];
            $purpose    = $purposes[array_rand($purposes)];
            $feeStatus  = $feeStatuses[array_rand($feeStatuses)];

            $ageAndCat  = $pickAgeAndCategory();
            $age        = $ageAndCat['age'];
            $cat        = $ageAndCat['cat'];

            // Nationality: if Foreign national category exists and visitor is foreign, use it
            $nationality = $origin['nat'];
            if ($nationality === 'Foreign' && !empty($manualCats)) {
                // Try to find a "Foreign" category
                $foreignCat = collect($manualCats)->first(fn($c) =>
                    stripos($c->category, 'foreign') !== false
                );
                if ($foreignCat) $cat = $foreignCat;
            }

            $townCity      = $origin['town'];
            $country       = $origin['country'];
            $placeOfOrigin = "{$townCity}, {$country}";
            $contact       = '09' . rand(100000000, 999999999);

            // Spread across past 90 days with more visits in recent weeks
            $daysAgo   = (int) round(abs(random_int(0, 90) - rand(0, 30)));
            $daysAgo   = min($daysAgo, 89);
            $arrivalAt = Carbon::now()->subDays($daysAgo)->setTime(rand(7, 18), rand(0, 59));

            $isDayTour = rand(1, 4) !== 1; // 75% day tour
            $nights    = $isDayTour ? null : rand(1, 4);
            $duration  = $isDayTour ? 'Day Tour' : "{$nights} night(s)";

            // Registration ID
            $regDate  = $arrivalAt->format('Ymd');
            $dailyCounts[$regDate] = ($dailyCounts[$regDate] ?? 0) + 1;
            $registrationId = 'BEL-' . $regDate . '-' . str_pad($dailyCounts[$regDate], 4, '0', STR_PAD_LEFT);

            // ── Profile ───────────────────────────────────────────────────────
            $profileId = (string) Str::uuid();
            DB::table('visitor_profiles')->insert([
                'id'              => $profileId,
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'middle_name'     => $middleName,
                'municipality'    => $townCity,
                'province'        => $country,
                'place_of_origin' => $placeOfOrigin,
                'contact_number'  => $contact,
                'created_at'      => $arrivalAt,
                'updated_at'      => $arrivalAt,
            ]);

            // ── Visit ─────────────────────────────────────────────────────────
            $visitId = (string) Str::uuid();
            DB::table('visitor_visits')->insert([
                'id'                       => $visitId,
                'registration_id'          => $registrationId,
                'profile_id'               => $profileId,
                'purpose'                  => $purpose,
                'purpose_other'            => null,
                'group_code'               => null,
                'duration_of_stay'         => $duration,
                'is_day_tour'              => $isDayTour ? 1 : 0,
                'nights'                   => $nights,
                'visitor_category'         => $cat->category,
                'sex'                      => $sex,
                'age'                      => $age,
                'nationality'              => $nationality,
                'town_city'                => $townCity,
                'country'                  => $country,
                'remarks'                  => null,
                'arrival_at'               => $arrivalAt,
                'snapshot_first_name'      => $firstName,
                'snapshot_middle_name'     => $middleName,
                'snapshot_last_name'       => $lastName,
                'snapshot_municipality'    => $townCity,
                'snapshot_province'        => $country,
                'snapshot_place_of_origin' => $placeOfOrigin,
                'snapshot_contact_number'  => $contact,
                'fee_status'               => $feeStatus,
                'waiver_reason'            => $feeStatus === 'Waived'
                                                ? $waivers[array_rand($waivers)]
                                                : null,
                'source'                   => 'staff',
                'registered_by'            => $adminId,
                'created_at'               => $arrivalAt,
                'updated_at'               => $arrivalAt,
            ]);

            // ── Destinations (1–3 real attractions) ───────────────────────────
            $numDest  = rand(1, min(3, count($attractionIds)));
            $selected = (array) array_rand(array_flip($attractionIds), $numDest);
            foreach ($selected as $attractionId) {
                try {
                    DB::table('visitor_destinations')->insert([
                        'visit_id'      => $visitId,
                        'attraction_id' => $attractionId,
                        'created_at'    => $arrivalAt,
                        'updated_at'    => $arrivalAt,
                    ]);
                } catch (\Exception $e) { /* skip duplicate */ }
            }

            // ── Receipt for Collected visits ──────────────────────────────────
            if ($feeStatus === 'Collected') {
                $fee       = (int) $cat->fee;
                $receiptId = (string) Str::uuid();
                $receiptNo = 'OR-' . $arrivalAt->format('Y') . '-'
                           . str_pad($orCounter++, 7, '0', STR_PAD_LEFT);

                DB::table('receipts')->insert([
                    'id'                 => $receiptId,
                    'receipt_number'     => $receiptNo,
                    'visit_id'           => $visitId,
                    'amount'             => $fee,
                    'currency'           => 'PHP',
                    'fee_type'           => 'Standard',
                    'number_of_visitors' => 1,
                    'total_amount'       => $fee,
                    'waiver_reason'      => null,
                    'payment_method'     => 'Cash',
                    'collected_by'       => $adminId,
                    'collected_at'       => $arrivalAt->copy()->addMinutes(rand(5, 30)),
                    'notes'              => null,
                    'member_breakdown'   => json_encode([[
                        'visit_id'         => $visitId,
                        'registration_id'  => $registrationId,
                        'full_name'        => "{$firstName} {$lastName}",
                        'visitor_category' => $cat->category,
                        'fee'              => $fee,
                    ]]),
                    'created_at'  => $arrivalAt,
                    'updated_at'  => $arrivalAt,
                ]);
            }

            // ── Audit log ─────────────────────────────────────────────────────
            DB::table('audit_logs')->insert([
                'id'          => (string) Str::uuid(),
                'user_id'     => $adminId,
                'action'      => 'visit_created',
                'module'      => 'visitor_visits',
                'target_type' => 'VisitorVisit',
                'target_id'   => $visitId,
                'new_values'  => json_encode([
                    'registration_id'  => $registrationId,
                    'visitor_category' => $cat->category,
                    'nationality'      => $nationality,
                ]),
                'ip_address'  => '127.0.0.1',
                'created_at'  => $arrivalAt,
            ]);

            if ($i % 10 === 0) {
                $this->command->info("  {$i}/100 visitors seeded...");
            }
        }

        $this->command->info('✓ 100 sample visitors seeded successfully.');
        $this->command->info('  Categories used: ' . $feeCategories->pluck('category')->implode(', '));
        $this->command->info('  Attractions used: ' . $attractions->pluck('name')->implode(', '));
    }
}