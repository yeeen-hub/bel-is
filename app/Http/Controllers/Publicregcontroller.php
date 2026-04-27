<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorProfile;
use App\Models\FeeCategory;
use App\Models\BarangayAttraction;
use App\Traits\SavesVisitorDestinations;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Inertia\Inertia;
use Carbon\Carbon;

class PublicRegController extends Controller
{
    use SavesVisitorDestinations;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateRegistrationId(): string
    {
        $date    = Carbon::now()->format('Ymd');
        $lockKey = "bel_reg_{$date}";

        $locked = DB::selectOne("SELECT GET_LOCK(?, 5) AS acquired", [$lockKey]);

        if (!$locked || !$locked->acquired) {
            throw new \RuntimeException('Could not acquire registration ID lock. Please try again.');
        }

        try {
            $count = VisitorVisit::whereDate('created_at', Carbon::today())->count();
            return 'BEL-' . $date . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        } finally {
            DB::selectOne("SELECT RELEASE_LOCK(?)", [$lockKey]);
        }
    }

    private function saveVisitWithUniqueCode(VisitorVisit $visit): string
    {
        $maxAttempts = 10;
        $attempts    = 0;

        while ($attempts < $maxAttempts) {
            $code              = 'BEL-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $visit->reference_code = $code;

            try {
                $visit->save();
                return $code;
            } catch (UniqueConstraintViolationException $e) {
                $attempts++;
                continue;
            }
        }

        throw new \RuntimeException('Could not generate a unique reference code after ' . $maxAttempts . ' attempts.');
    }

    // ── Derive visitor_category from age using DB fee_categories ─────────────
    // Parses age_range strings like "60 Yrs. / Above", "21-59 YRS. OLD",
    // "12 Yrs. / Below", "0-12" etc. — never hardcoded.
    private function deriveCategoryFromAge(?int $age): ?string
    {
        if ($age === null) return null;

        foreach (FeeCategory::all() as $cat) {
            $range = strtolower($cat->age_range ?? '');

            // "above" / "abov" patterns → min age upward
            if (str_contains($range, 'above') || str_contains($range, 'abov')) {
                preg_match('/(\d+)/', $range, $m);
                if (isset($m[1]) && $age >= (int) $m[1]) return $cat->category;
            }

            // "below" patterns → max age downward
            if (str_contains($range, 'below')) {
                preg_match('/(\d+)/', $range, $m);
                if (isset($m[1]) && $age <= (int) $m[1]) return $cat->category;
            }

            // "X-Y" range pattern
            if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', $range, $m)) {
                if ($age >= (int) $m[1] && $age <= (int) $m[2]) return $cat->category;
            }
        }

        return null;
    }

    // ── Public page ───────────────────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('PublicRegPage', [
            'feeCategories' => FeeCategory::orderBy('id')->get(['id', 'category', 'age_range', 'fee']),
            'barangayAttractions' => BarangayAttraction::with('sitio')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($a) => [
                    'id'         => $a->id,
                    'name'       => $a->name,
                    'type'       => $a->type,
                    'sitio_name' => $a->sitio?->name,
                ]),
            // Form field settings — controls visibility/required on public form
            'formFields' => DB::table('form_field_settings')
                ->orderBy('sort_order')
                ->get()
                ->map(fn($f) => [
                    'field_key'   => $f->field_key,
                    'label'       => $f->label,
                    'is_required' => (bool) $f->is_required,
                    'is_visible'  => (bool) $f->is_visible,
                ])
                ->values(),
        ]);
    }

    // ── Individual pre-registration ───────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            // New Tourist Arrival Form fields
            'town_city'        => 'required|string|max:255',
            'country'          => 'nullable|string|max:255',
            'sex'              => 'nullable|in:M,F',
            'age'              => 'nullable|integer|min:0|max:120',
            'nationality'      => 'nullable|in:Local,Aklanon,OFW,Foreign',
            'remarks'          => 'nullable|string|max:1000',
            'is_day_tour'      => 'nullable|boolean',
            'nights'           => 'nullable|integer|min:1',
            // Legacy
            'municipality'     => 'nullable|string|max:255',
            'province'         => 'nullable|string|max:255',
            'purpose'          => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'purpose_other'    => 'required_if:purpose,Other|nullable|string|max:255',
            'duration_of_stay' => 'required|string|max:255',
            'contact_number'   => 'nullable|string|max:20',
            'visitor_category' => 'nullable|string|max:100',
            'destinations'                     => 'nullable|array',
            'destinations.*.attraction_id'     => 'nullable|integer|exists:barangay_attractions,id',
            'destinations.*.other_destination' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Derive category from age if not explicitly set
            $visitorCategory = $request->visitor_category
                ?? $this->deriveCategoryFromAge($request->age);

            $townCity = $request->town_city ?? $request->municipality ?? '';
            $country  = $request->country  ?? 'Philippines';

            [$visit, $referenceCode] = $this->createPreRegVisit(
                first_name:       $request->first_name,
                last_name:        $request->last_name,
                middle_name:      $request->middle_name,
                town_city:        $townCity,
                country:          $country,
                sex:              $request->sex,
                age:              $request->age ? (int) $request->age : null,
                nationality:      $request->nationality,
                remarks:          $request->remarks,
                contact:          $request->contact_number,
                purpose:          $request->purpose,
                purposeOther:     $request->purpose_other,
                duration:         $request->duration_of_stay,
                is_day_tour:      $request->boolean('is_day_tour', true),
                nights:           $request->nights,
                visitor_category: $visitorCategory,
                destinations:     $request->destinations ?? [],
            );

            DB::commit();

            return back()->with([
                'success'          => true,
                'mode'             => 'single',
                'reference_code'   => $referenceCode,
                'visit_id'         => $visit->id,
                'full_name'        => "{$request->first_name} {$request->last_name}",
                'visitor_category' => $visitorCategory,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    // ── Group pre-registration ────────────────────────────────────────────────
    public function storeGroup(Request $request)
    {
        $request->validate([
            'members'                                        => 'required|array|min:2|max:20',
            'members.*.first_name'                           => 'required|string|max:255',
            'members.*.last_name'                            => 'required|string|max:255',
            'members.*.middle_name'                          => 'nullable|string|max:255',
            'members.*.town_city'                            => 'required|string|max:255',
            'members.*.country'                              => 'nullable|string|max:255',
            'members.*.sex'                                  => 'nullable|in:M,F',
            'members.*.age'                                  => 'nullable|integer|min:0|max:120',
            'members.*.nationality'                          => 'nullable|in:Local,Aklanon,OFW,Foreign',
            'members.*.remarks'                              => 'nullable|string|max:1000',
            'members.*.purpose'                              => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'members.*.purpose_other'                        => 'nullable|string|max:255',
            'members.*.duration_of_stay'                     => 'required|string|max:255',
            'members.*.contact_number'                       => 'nullable|string|max:20',
            'members.*.visitor_category'                     => 'nullable|string|max:100',
            'members.*.is_day_tour'                          => 'nullable|boolean',
            'members.*.nights'                               => 'nullable|integer|min:1',
            'members.*.destinations'                         => 'nullable|array',
            'members.*.destinations.*.attraction_id'         => 'nullable|integer|exists:barangay_attractions,id',
            'members.*.destinations.*.other_destination'     => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $results = [];

            foreach ($request->members as $index => $member) {
                $visitorCategory = $member['visitor_category']
                    ?? $this->deriveCategoryFromAge(
                        isset($member['age']) ? (int) $member['age'] : null
                    );

                $townCity = $member['town_city'] ?? $member['municipality'] ?? '';
                $country  = $member['country']   ?? 'Philippines';

                [$visit, $referenceCode] = $this->createPreRegVisit(
                    first_name:       $member['first_name'],
                    last_name:        $member['last_name'],
                    middle_name:      $member['middle_name'] ?? null,
                    town_city:        $townCity,
                    country:          $country,
                    sex:              $member['sex'] ?? null,
                    age:              isset($member['age']) ? (int) $member['age'] : null,
                    nationality:      $member['nationality'] ?? null,
                    remarks:          $member['remarks'] ?? null,
                    contact:          $member['contact_number'] ?? null,
                    purpose:          $member['purpose'],
                    purposeOther:     $member['purpose_other'] ?? null,
                    duration:         $member['duration_of_stay'],
                    is_day_tour:      (bool) ($member['is_day_tour'] ?? true),
                    nights:           $member['nights'] ?? null,
                    visitor_category: $visitorCategory,
                    destinations:     $member['destinations'] ?? [],
                );

                $results[] = [
                    'visit_id'         => $visit->id,
                    'full_name'        => "{$member['first_name']} {$member['last_name']}",
                    'reference_code'   => $referenceCode,
                    'visitor_category' => $visitorCategory,
                    'is_leader'        => $index === 0,
                ];
            }

            $groupCode = $results[0]['reference_code'];
            DB::table('visitor_visits')
                ->whereIn('id', array_column($results, 'visit_id'))
                ->update(['group_code' => $groupCode]);

            DB::commit();

            return back()->with([
                'success'    => true,
                'mode'       => 'group',
                'group_code' => $groupCode,
                'members'    => $results,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Group registration failed: ' . $e->getMessage()]);
        }
    }

    // ── Reference code lookup ─────────────────────────────────────────────────
    public function lookup(Request $request)
    {
        $request->validate(['code' => 'required|string|min:3|max:20']);
        $code = strtoupper(trim($request->code));

        $visit = VisitorVisit::where('reference_code', $code)
            ->where('source', 'pre_registration')
            ->where('fee_status', 'Pending')
            ->whereNull('deleted_at')
            ->first();

        if (!$visit) {
            return response()->json([
                'found'   => false,
                'message' => 'No pending pre-registration found for this code.',
            ], 404);
        }

        $isGroup = !empty($visit->group_code);

        if ($isGroup) {
            $groupVisits = VisitorVisit::where('group_code', $visit->group_code)
                ->where('source', 'pre_registration')
                ->where('fee_status', 'Pending')
                ->whereNull('deleted_at')
                ->get();

            return response()->json([
                'found'    => true,
                'is_group' => true,
                'visit'    => $this->formatVisit($visit),
                'members'  => $groupVisits->map(fn($gv) => $this->formatVisit($gv))->values()->all(),
            ]);
        }

        return response()->json([
            'found'    => true,
            'is_group' => false,
            'visit'    => $this->formatVisit($visit),
            'members'  => [$this->formatVisit($visit)],
        ]);
    }

    // ── Format visit for lookup response ─────────────────────────────────────
    private function formatVisit(VisitorVisit $visit): array
    {
        $visit->load('destinations.attraction', 'profile');

        return [
            'visit_id'         => $visit->id,
            'reference_code'   => $visit->reference_code,
            'registration_id'  => $visit->registration_id,
            'first_name'       => $visit->snapshot_first_name,
            'last_name'        => $visit->snapshot_last_name,
            // middle_name is stored in visitor_profiles (not in visit snapshot)
            'middle_name'      => $visit->profile?->middle_name ?? '',
            'town_city'        => $visit->town_city ?? $visit->snapshot_municipality,
            'country'          => $visit->country   ?? $visit->snapshot_province,
            'municipality'     => $visit->snapshot_municipality,
            'province'         => $visit->snapshot_province,
            'place_of_origin'  => $visit->snapshot_place_of_origin,
            'sex'              => $visit->sex,
            'age'              => $visit->age,
            'nationality'      => $visit->nationality,
            'remarks'          => $visit->remarks,
            'contact_number'   => $visit->snapshot_contact_number,
            'purpose'          => $visit->purpose,
            'purpose_other'    => $visit->purpose_other,
            'duration_of_stay' => $visit->duration_of_stay,
            'is_day_tour'      => (bool) ($visit->is_day_tour ?? true),
            'nights'           => $visit->nights,
            'visitor_category' => $visit->visitor_category,
            'destinations'     => $visit->destinations->map(fn($d) => [
                'attraction_id'     => $d->attraction_id,
                'other_destination' => $d->other_destination,
            ])->values()->toArray(),
            'fee_status'  => $visit->fee_status,
            'is_leader'   => $visit->group_code === $visit->reference_code,
            'created_at'  => Carbon::parse($visit->created_at)->format('M d, Y h:i A'),
        ];
    }

    // ── Create a single pre-registration visit ────────────────────────────────
    private function createPreRegVisit(
        string  $first_name,
        string  $last_name,
        ?string $middle_name,
        string  $town_city,
        string  $country,
        ?string $sex,
        ?int    $age,
        ?string $nationality,
        ?string $remarks,
        ?string $contact,
        string  $purpose,
        ?string $purposeOther,
        string  $duration,
        bool    $is_day_tour = true,
        ?int    $nights = null,
        ?string $visitor_category = null,
        array   $destinations = [],
    ): array {
        $profile = VisitorProfile::create([
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'middle_name'     => $middle_name ? trim($middle_name) : null,  // ← was missing
            'municipality'    => $town_city,
            'province'        => $country,
            'place_of_origin' => "{$town_city}, {$country}",
            'contact_number'  => $contact,
        ]);

        $registrationId = $this->generateRegistrationId();

        $visit = new VisitorVisit([
            'registration_id'  => $registrationId,
            'profile_id'       => $profile->id,
            'purpose'          => $purpose,
            'purpose_other'    => $purpose === 'Other' ? $purposeOther : null,
            'duration_of_stay' => $duration,
            'is_day_tour'      => $is_day_tour,
            'nights'           => $is_day_tour ? null : $nights,
            'visitor_category' => $visitor_category,
            'sex'              => $sex,
            'age'              => $age,
            'nationality'      => $nationality,
            'town_city'        => $town_city,
            'country'          => $country,
            'remarks'          => $remarks,
            'fee_status'       => 'Pending',
            'source'           => 'pre_registration',
            'registered_by'    => null,
        ]);

        $visit->takeSnapshot($profile);

        $referenceCode = $this->saveVisitWithUniqueCode($visit);

        $this->saveDestinations($visit->id, $destinations);

        return [$visit, $referenceCode];
    }
}