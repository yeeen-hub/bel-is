<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorProfile;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class PublicRegController extends Controller
{
    // ── Show public pre-registration page ─────────────────────────────────────
    public function create()
    {
        return Inertia::render('PublicRegPage');
    }

    // ── Individual pre-registration ───────────────────────────────────────────
    // POST /pre-register → creates 1 VisitorProfile + 1 VisitorVisit
    public function store(Request $request)
    {
        $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'municipality'     => 'required|string|max:255',
            'province'         => 'required|string|max:255',
            'purpose'          => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'purpose_other'    => 'required_if:purpose,Other|nullable|string|max:255',
            'duration_of_stay' => 'required|string|max:255',
            'contact_number'   => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            [$visit, $referenceCode] = $this->createPreRegVisit(
                first_name:   $request->first_name,
                last_name:    $request->last_name,
                municipality: $request->municipality,
                province:     $request->province,
                contact:      $request->contact_number,
                purpose:      $request->purpose,
                purposeOther: $request->purpose_other,
                duration:     $request->duration_of_stay,
                // group_code is null for individual registrations
            );

            DB::commit();

            return back()->with([
                'success'        => true,
                'mode'           => 'single',
                'reference_code' => $referenceCode,
                'visit_id'       => $visit->id,
                'full_name'      => "{$request->first_name} {$request->last_name}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    // ── Group pre-registration ────────────────────────────────────────────────
    // POST /pre-register/group
    // Each member → own VisitorProfile + VisitorVisit + unique reference_code.
    // All members share the leader's reference_code as group_code so staff
    // can look up the whole group by entering any one member's code.
    //
    // visitor_visits.group_code (varchar, nullable, indexed):
    //   - NULL  → individual visit
    //   - 'BEL-XXXXXX' → group visit, value = leader's reference_code
    public function storeGroup(Request $request)
    {
        $request->validate([
            'members'                    => 'required|array|min:2|max:20',
            'members.*.first_name'       => 'required|string|max:255',
            'members.*.last_name'        => 'required|string|max:255',
            'members.*.municipality'     => 'required|string|max:255',
            'members.*.province'         => 'required|string|max:255',
            'members.*.purpose'          => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'members.*.purpose_other'    => 'nullable|string|max:255',
            'members.*.duration_of_stay' => 'required|string|max:255',
            'members.*.contact_number'   => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // ── Step 1: Create all visits WITHOUT group_code ──────────────────
            // We don't know the leader's reference_code until the first visit
            // is saved, so we can't set group_code during construction.
            $results = [];

            foreach ($request->members as $index => $member) {
                [$visit, $referenceCode] = $this->createPreRegVisit(
                    first_name:   $member['first_name'],
                    last_name:    $member['last_name'],
                    municipality: $member['municipality'],
                    province:     $member['province'],
                    contact:      $member['contact_number'] ?? null,
                    purpose:      $member['purpose'],
                    purposeOther: $member['purpose_other'] ?? null,
                    duration:     $member['duration_of_stay'],
                    // group_code set in Step 2
                );

                $results[] = [
                    'visit_id'       => $visit->id,
                    'full_name'      => "{$member['first_name']} {$member['last_name']}",
                    'reference_code' => $referenceCode,
                    'is_leader'      => $index === 0,
                ];
            }

            // ── Step 2: Set group_code on ALL members using DB::table() ───────
            // Using DB::table() bypasses $fillable and Eloquent model events,
            // guaranteeing the column is written regardless of model config.
            // group_code = leader's reference_code (used by lookup() to find group)
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
    // GET /pre-register/lookup?code=BEL-XXXXXX
    // Called via axios from AdminRegPage.vue when staff enters a reference code.
    // If the code belongs to a group, returns ALL group members.
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

        // group_code is non-null → this visit is part of a group
        $isGroup = !empty($visit->group_code);

        if ($isGroup) {
            // Fetch all members sharing the same group_code
            $groupVisits = VisitorVisit::where('group_code', $visit->group_code)
                ->where('source', 'pre_registration')
                ->where('fee_status', 'Pending')
                ->whereNull('deleted_at')
                ->get();

            $members = $groupVisits->map(fn($gv) => $this->formatVisit($gv))->values()->all();

            return response()->json([
                'found'    => true,
                'is_group' => true,
                'visit'    => $this->formatVisit($visit),
                'members'  => $members,
            ]);
        }

        return response()->json([
            'found'    => true,
            'is_group' => false,
            'visit'    => $this->formatVisit($visit),
            'members'  => [$this->formatVisit($visit)],
        ]);
    }

    // ── Format a visit for JSON ───────────────────────────────────────────────
    private function formatVisit(VisitorVisit $visit): array
    {
        return [
            'visit_id'        => $visit->id,
            'reference_code'  => $visit->reference_code,
            'registration_id' => $visit->registration_id,
            'first_name'      => $visit->snapshot_first_name,
            'last_name'       => $visit->snapshot_last_name,
            'municipality'    => $visit->snapshot_municipality,
            'province'        => $visit->snapshot_province,
            'place_of_origin' => $visit->snapshot_place_of_origin,
            'contact_number'  => $visit->snapshot_contact_number,
            'purpose'         => $visit->purpose,
            'purpose_other'   => $visit->purpose_other,
            'duration_of_stay'=> $visit->duration_of_stay,
            'fee_status'      => $visit->fee_status,
            // is_leader: true if this visit's reference_code IS the group_code
            'is_leader'       => $visit->group_code === $visit->reference_code,
            'created_at'      => Carbon::parse($visit->created_at)->format('M d, Y h:i A'),
        ];
    }

    // ── Shared: create one VisitorProfile + VisitorVisit ─────────────────────
    // group_code is NOT set here — it's written in Step 2 of storeGroup()
    // using DB::table() after we know the leader's reference_code.
    private function createPreRegVisit(
        string  $first_name,
        string  $last_name,
        string  $municipality,
        string  $province,
        ?string $contact,
        string  $purpose,
        ?string $purposeOther,
        string  $duration,
    ): array {
        $profile = VisitorProfile::create([
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'municipality'    => $municipality,
            'province'        => $province,
            'place_of_origin' => "{$municipality}, {$province}",
            'contact_number'  => $contact,
        ]);

        // Sequential registration ID: BEL-20260405-0001
        $registrationId = 'BEL-' . Carbon::now()->format('Ymd') . '-' . str_pad(
            VisitorVisit::whereDate('created_at', Carbon::today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        // Unique 6-digit reference code: BEL-482951
        do {
            $referenceCode = 'BEL-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (VisitorVisit::where('reference_code', $referenceCode)->exists());

        $visit = new VisitorVisit([
            'registration_id'  => $registrationId,
            'reference_code'   => $referenceCode,
            'profile_id'       => $profile->id,
            'purpose'          => $purpose,
            'purpose_other'    => $purpose === 'Other' ? $purposeOther : null,
            'duration_of_stay' => $duration,
            'fee_status'       => 'Pending',
            'source'           => 'pre_registration',
            'registered_by'    => null,
            // group_code intentionally omitted — set later by storeGroup()
        ]);

        $visit->takeSnapshot($profile);
        $visit->save();

        return [$visit, $referenceCode];
    }
}