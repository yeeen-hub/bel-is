<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorProfile;
use App\Models\FeeCategory;
use App\Models\BarangayAttraction;
use App\Models\AuditLog;
use App\Traits\SavesVisitorDestinations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\UniqueConstraintViolationException;
use Inertia\Inertia;
use Carbon\Carbon;

class VisitorController extends Controller
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

    private function generateReferenceCode(): string
    {
        do {
            $code   = 'BEL-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = VisitorVisit::where('reference_code', $code)->exists();
        } while ($exists);

        return $code;
    }

    // ── List all visits ───────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = VisitorVisit::with('receipt', 'registeredBy')
            ->where('source', 'staff')
            ->latest('arrival_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('snapshot_first_name',       'like', "%{$search}%")
                  ->orWhere('snapshot_last_name',       'like', "%{$search}%")
                  ->orWhere('snapshot_place_of_origin', 'like', "%{$search}%")
                  ->orWhere('registration_id',          'like', "%{$search}%")
                  ->orWhere('reference_code',           'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(snapshot_first_name, ' ', snapshot_last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('destination_id')) {
            $query->whereHas('destinations', fn($q) =>
                $q->where('attraction_id', $request->destination_id)
            );
        }

        if ($request->filled('purpose'))    $query->where('purpose',    $request->purpose);
        if ($request->filled('fee_status')) $query->where('fee_status', $request->fee_status);
        if ($request->filled('date_from'))  $query->whereDate('arrival_at', '>=', $request->date_from);
        if ($request->filled('date_to'))    $query->whereDate('arrival_at', '<=', $request->date_to);

        $visits = $query->paginate(10)->withQueryString();

        return Inertia::render('AdminVRPage', [
            'visitors' => $visits->through(fn($v) => [
                'id'               => $v->id,
                'registration_id'  => $v->registration_id,
                'reference_code'   => $v->reference_code,
                'name'             => $v->full_name,
                'place_of_origin'  => $v->place_of_origin,
                'municipality'     => $v->snapshot_municipality,
                'province'         => $v->snapshot_province,
                'purpose'          => $v->purpose === 'Other' && $v->purpose_other
                                         ? "Other: {$v->purpose_other}"
                                         : $v->purpose,
                'duration'         => $v->duration_of_stay,
                'visitor_category' => $v->visitor_category,
                'contact_number'   => $v->snapshot_contact_number ?? 'N/A',
                'fee_status'       => $v->fee_status,
                'arrival_at'       => $v->arrival_at->format('M d, Y'),
                'registered_by'    => $v->registeredBy->name ?? 'N/A',
            ]),
            'filters' => [
                'search'         => $request->search         ?? '',
                'purpose'        => $request->purpose        ?? '',
                'fee_status'     => $request->fee_status     ?? '',
                'date_from'      => $request->date_from      ?? '',
                'date_to'        => $request->date_to        ?? '',
                'destination_id' => $request->destination_id ?? '',
            ],
            'attractionOptions' => BarangayAttraction::where('is_active', true)
                ->orderBy('name')->get(['id', 'name']),
            'pendingFees' => VisitorVisit::where('source', 'staff')
                                         ->where('fee_status', 'Pending')
                                         ->count(),
        ]);
    }

    // ── Show registration form ────────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('AdminRegPage', [
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

    // ── Store a single visit ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'town_city'        => 'required|string|max:255',
            'country'          => 'nullable|string|max:255',
            'sex'              => 'nullable|in:M,F',
            'age'              => 'nullable|integer|min:0|max:120',
            'nationality'      => 'nullable|in:Local,Aklanon,OFW,Foreign',
            'remarks'          => 'nullable|string|max:1000',
            'is_day_tour'      => 'nullable|boolean',
            'nights'           => 'nullable|integer|min:1',
            'municipality'     => 'nullable|string|max:255',
            'province'         => 'nullable|string|max:255',
            'place_of_origin'  => 'required|string|max:255',
            'purpose'          => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'purpose_other'    => 'required_if:purpose,Other|nullable|string|max:255',
            'duration_of_stay' => 'required|string|max:255',
            'contact_number'   => 'nullable|string|max:20',
            'visitor_category' => 'required|string|max:100',
            'profile_id'       => 'nullable|uuid|exists:visitor_profiles,id',
            'visit_id'         => 'nullable|uuid|exists:visitor_visits,id',
            'destinations'                     => 'nullable|array',
            'destinations.*.attraction_id'     => 'nullable|integer|exists:barangay_attractions,id',
            'destinations.*.other_destination' => 'nullable|string|max:255',
        ]);

        // Resolve shared fields
        $townCity    = $request->town_city  ?? $request->municipality ?? '';
        $country     = $request->country    ?? $request->province     ?? 'Philippines';
        $middleName  = $request->middle_name ? trim($request->middle_name) : null;
        $nationality = $request->nationality ?: null;
        $sex         = $request->sex         ?: null;
        $age         = ($request->age !== null && $request->age !== '') ? (int) $request->age : null;

        DB::beginTransaction();

        try {
            // ── SCENARIO 1: Pre-registered visitor ───────────────────────────
            if ($request->filled('visit_id')) {
                $visit = VisitorVisit::findOrFail($request->visit_id);

                if ($visit->source !== 'pre_registration' || $visit->fee_status !== 'Pending') {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'This registration has already been processed.']);
                }

                // Update profile with middle_name
                if ($visit->profile_id) {
                    $profile = VisitorProfile::find($visit->profile_id);
                    if ($profile) {
                        $profile->update([
                            'middle_name'     => $middleName ?? $profile->middle_name,
                            'municipality'    => $townCity,
                            'province'        => $country,
                            'place_of_origin' => "{$townCity}, {$country}",
                            'contact_number'  => $request->contact_number ?? $profile->contact_number,
                        ]);
                    }
                }

                // Update visit with ALL new Tourist Arrival Form fields
                $visit->update([
                    'source'                   => 'staff',
                    'registered_by'            => Auth::id(),
                    'purpose'                  => $request->purpose,
                    'purpose_other'            => $request->purpose === 'Other' ? $request->purpose_other : null,
                    'duration_of_stay'         => $request->duration_of_stay,
                    'is_day_tour'              => $request->boolean('is_day_tour', true),
                    'nights'                   => $request->nights,
                    'visitor_category'         => $request->visitor_category,
                    // Tourist Arrival Form fields — were missing in original
                    'sex'                      => $sex,
                    'age'                      => $age,
                    'nationality'              => $nationality,
                    'town_city'                => $townCity,
                    'country'                  => $country,
                    'remarks'                  => $request->remarks ?: null,
                    // Snapshots
                    'snapshot_first_name'      => $request->first_name,
                    'snapshot_middle_name'     => $middleName,
                    'snapshot_last_name'       => $request->last_name,
                    'snapshot_municipality'    => $townCity,
                    'snapshot_province'        => $country,
                    'snapshot_place_of_origin' => "{$townCity}, {$country}",
                    'snapshot_contact_number'  => $request->contact_number,
                ]);

                $this->saveDestinations($visit->id, $request->destinations ?? []);

                AuditLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'pre_registration_confirmed',
                    'module'      => 'visitor_visits',
                    'target_type' => 'VisitorVisit',
                    'target_id'   => $visit->id,
                    'new_values'  => $visit->fresh()->toArray(),
                    'ip_address'  => $request->ip(),
                ]);

                DB::commit();
                return redirect()->route('adminpay', $visit->id)
                    ->with('success', 'Pre-registration confirmed. Proceed to payment.');
            }

            // ── SCENARIO 2: New or returning walk-in ─────────────────────────
            if ($request->filled('profile_id')) {
                $profile = VisitorProfile::findOrFail($request->profile_id);
                $profile->update([
                    'middle_name'     => $middleName ?? $profile->middle_name,
                    'municipality'    => $townCity,
                    'province'        => $country,
                    'place_of_origin' => "{$townCity}, {$country}",
                    'contact_number'  => $request->contact_number ?? $profile->contact_number,
                ]);
            } else {
                $profile = VisitorProfile::create([
                    'first_name'      => $request->first_name,
                    'last_name'       => $request->last_name,
                    'middle_name'     => $middleName,
                    'municipality'    => $townCity,
                    'province'        => $country,
                    'place_of_origin' => "{$townCity}, {$country}",
                    'contact_number'  => $request->contact_number,
                ]);
            }

            $registrationId = $this->generateRegistrationId();

            $visit = new VisitorVisit([
                'registration_id'  => $registrationId,
                'profile_id'       => $profile->id,
                'purpose'          => $request->purpose,
                'purpose_other'    => $request->purpose === 'Other' ? $request->purpose_other : null,
                'duration_of_stay' => $request->duration_of_stay,
                'is_day_tour'      => $request->boolean('is_day_tour', true),
                'nights'           => $request->nights,
                'visitor_category' => $request->visitor_category,
                'sex'              => $sex,
                'age'              => $age,
                'nationality'      => $nationality,
                'town_city'        => $townCity,
                'country'          => $country,
                'remarks'          => $request->remarks ?: null,
                'fee_status'       => 'Pending',
                'source'           => 'staff',
                'registered_by'    => Auth::id(),
            ]);

            $visit->takeSnapshot($profile);
            $visit->save();

            $this->saveDestinations($visit->id, $request->destinations ?? []);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'visit_created',
                'module'      => 'visitor_visits',
                'target_type' => 'VisitorVisit',
                'target_id'   => $visit->id,
                'new_values'  => $visit->toArray(),
                'ip_address'  => $request->ip(),
            ]);

            DB::commit();
            return redirect()->route('adminpay', $visit->id)
                ->with('success', 'Visitor registered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    // ── Store GROUP ───────────────────────────────────────────────────────────
    public function storeGroup(Request $request)
    {
        $request->validate([
            'members'                                        => 'required|array|min:1|max:20',
            'members.*.first_name'                           => 'required|string|max:255',
            'members.*.last_name'                            => 'required|string|max:255',
            'members.*.middle_name'                          => 'nullable|string|max:255',
            'members.*.town_city'                            => 'nullable|string|max:255',
            'members.*.country'                              => 'nullable|string|max:255',
            'members.*.sex'                                  => 'nullable|in:M,F',
            'members.*.age'                                  => 'nullable|integer|min:0|max:120',
            'members.*.nationality'                          => 'nullable|in:Local,Aklanon,OFW,Foreign',
            'members.*.remarks'                              => 'nullable|string|max:1000',
            'members.*.is_day_tour'                          => 'nullable|boolean',
            'members.*.nights'                               => 'nullable|integer|min:1',
            'members.*.municipality'                         => 'nullable|string|max:255',
            'members.*.province'                             => 'nullable|string|max:255',
            'members.*.place_of_origin'                      => 'nullable|string|max:255',
            'members.*.purpose'                              => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'members.*.purpose_other'                        => 'nullable|string|max:255',
            'members.*.duration_of_stay'                     => 'required|string|max:255',
            'members.*.visitor_category'                     => 'nullable|string|max:100',
            'members.*.contact_number'                       => 'nullable|string|max:20',
            'members.*.profile_id'                           => 'nullable|uuid|exists:visitor_profiles,id',
            'members.*.visit_id'                             => 'nullable|uuid|exists:visitor_visits,id',
            'members.*.destinations'                         => 'nullable|array',
            'members.*.destinations.*.attraction_id'         => 'nullable|integer|exists:barangay_attractions,id',
            'members.*.destinations.*.other_destination'     => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $visits = [];

            foreach ($request->members as $memberData) {
                if (!empty($memberData['visit_id'])) {
                    $visit = VisitorVisit::findOrFail($memberData['visit_id']);
                    if ($visit->source === 'pre_registration' && $visit->fee_status === 'Pending') {

                        $townCity   = $memberData['town_city']   ?? $memberData['municipality'] ?? '';
                        $country    = $memberData['country']     ?? $memberData['province']     ?? 'Philippines';
                        $middleName = isset($memberData['middle_name']) ? trim($memberData['middle_name']) : null;
                        $nationality = $memberData['nationality'] ?: null;
                        $sex        = $memberData['sex']         ?: null;
                        $age        = (isset($memberData['age']) && $memberData['age'] !== '') ? (int) $memberData['age'] : null;

                        // Update profile
                        if ($visit->profile_id) {
                            $profile = VisitorProfile::find($visit->profile_id);
                            if ($profile) {
                                $profile->update([
                                    'middle_name'     => $middleName ?? $profile->middle_name,
                                    'municipality'    => $townCity,
                                    'province'        => $country,
                                    'place_of_origin' => "{$townCity}, {$country}",
                                    'contact_number'  => $memberData['contact_number'] ?? $profile->contact_number,
                                ]);
                            }
                        }

                        $visit->update([
                            'source'                   => 'staff',
                            'registered_by'            => Auth::id(),
                            'purpose'                  => $memberData['purpose'],
                            'purpose_other'            => $memberData['purpose'] === 'Other' ? ($memberData['purpose_other'] ?? null) : null,
                            'duration_of_stay'         => $memberData['duration_of_stay'],
                            'is_day_tour'              => (bool) ($memberData['is_day_tour'] ?? true),
                            'nights'                   => $memberData['nights'] ?? null,
                            'visitor_category'         => $memberData['visitor_category'] ?? null,
                            'sex'                      => $sex,
                            'age'                      => $age,
                            'nationality'              => $nationality,
                            'town_city'                => $townCity,
                            'country'                  => $country,
                            'remarks'                  => $memberData['remarks'] ?: null,
                            'snapshot_first_name'      => $memberData['first_name'],
                            'snapshot_middle_name'     => $middleName,
                            'snapshot_last_name'       => $memberData['last_name'],
                            'snapshot_municipality'    => $townCity,
                            'snapshot_province'        => $country,
                            'snapshot_place_of_origin' => "{$townCity}, {$country}",
                            'snapshot_contact_number'  => $memberData['contact_number'] ?? null,
                        ]);

                        $this->saveDestinations($visit->id, $memberData['destinations'] ?? []);
                        $visits[] = $visit;
                        continue;
                    }
                }

                [$visit] = $this->createVisitRecord($memberData, Auth::id());
                $visits[] = $visit;
            }

            if (count($visits) > 1) {
                $groupCode = $visits[0]->registration_id;
                $visitIds  = collect($visits)->pluck('id')->toArray();
                DB::table('visitor_visits')
                    ->whereIn('id', $visitIds)
                    ->update(['group_code' => $groupCode]);
            }

            DB::commit();
            return redirect()->route('adminpay', $visits[0]->id)
                ->with('success', count($visits) . ' visitors registered. Processing payment.')
                ->with('group_visit_ids', collect($visits)->pluck('id')->toArray());

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Group registration failed: ' . $e->getMessage()]);
        }
    }

    // ── createVisitRecord — used by storeGroup ────────────────────────────────
    private function createVisitRecord(array $data, int $staffId): array
    {
        $townCity      = $data['town_city']    ?? $data['municipality'] ?? '';
        $country       = $data['country']      ?? $data['province']     ?? 'Philippines';
        $middleName    = isset($data['middle_name']) ? trim($data['middle_name']) : null;
        $nationality   = $data['nationality']  ?: null;
        $sex           = $data['sex']          ?: null;
        $age           = (isset($data['age']) && $data['age'] !== '') ? (int) $data['age'] : null;
        $placeOfOrigin = "{$townCity}, {$country}";

        if (!empty($data['profile_id'])) {
            $profile = VisitorProfile::findOrFail($data['profile_id']);
            $profile->update([
                'middle_name'     => $middleName ?? $profile->middle_name,
                'municipality'    => $townCity,
                'province'        => $country,
                'place_of_origin' => $placeOfOrigin,
                'contact_number'  => $data['contact_number'] ?? $profile->contact_number,
            ]);
        } else {
            $profile = VisitorProfile::create([
                'first_name'      => $data['first_name'],
                'last_name'       => $data['last_name'],
                'middle_name'     => $middleName,
                'municipality'    => $townCity,
                'province'        => $country,
                'place_of_origin' => $placeOfOrigin,
                'contact_number'  => $data['contact_number'] ?? null,
            ]);
        }

        $registrationId = $this->generateRegistrationId();

        $visit = new VisitorVisit([
            'registration_id'  => $registrationId,
            'profile_id'       => $profile->id,
            'purpose'          => $data['purpose'],
            'purpose_other'    => $data['purpose'] === 'Other' ? ($data['purpose_other'] ?? null) : null,
            'duration_of_stay' => $data['duration_of_stay'],
            'is_day_tour'      => isset($data['is_day_tour']) ? (bool) $data['is_day_tour'] : true,
            'nights'           => $data['nights'] ?? null,
            'visitor_category' => $data['visitor_category'] ?? null,
            'sex'              => $sex,
            'age'              => $age,
            'nationality'      => $nationality,
            'town_city'        => $townCity,
            'country'          => $country,
            'remarks'          => $data['remarks'] ?: null,
            'fee_status'       => 'Pending',
            'source'           => 'staff',
            'registered_by'    => $staffId,
        ]);

        $visit->takeSnapshot($profile);
        $visit->save();

        $this->saveDestinations($visit->id, $data['destinations'] ?? []);

        AuditLog::create([
            'user_id'     => $staffId,
            'action'      => 'visit_created',
            'module'      => 'visitor_visits',
            'target_type' => 'VisitorVisit',
            'target_id'   => $visit->id,
            'new_values'  => $visit->toArray(),
            'ip_address'  => request()->ip(),
        ]);

        return [$visit, $profile];
    }

    // ── Show single visit ─────────────────────────────────────────────────────
    public function show(VisitorVisit $visitor)
    {
        $visitor->load('receipt', 'registeredBy', 'profile', 'destinations.attraction');

        return Inertia::render('AdminVRShowPage', [
            'visitor' => [
                'id'               => $visitor->id,
                'registration_id'  => $visitor->registration_id,
                'reference_code'   => $visitor->reference_code,
                'profile_id'       => $visitor->profile_id,
                'first_name'       => $visitor->snapshot_first_name,
                'last_name'        => $visitor->snapshot_last_name,
                'full_name'        => $visitor->full_name,
                'place_of_origin'  => $visitor->place_of_origin,
                'municipality'     => $visitor->snapshot_municipality,
                'province'         => $visitor->snapshot_province,
                'contact_number'   => $visitor->snapshot_contact_number ?? 'N/A',
                'visitor_category' => $visitor->visitor_category,
                'sex'              => $visitor->sex,
                'age'              => $visitor->age,
                'nationality'      => $visitor->nationality,
                'country'          => $visitor->country,
                'town_city'        => $visitor->town_city,
                'destinations'     => $visitor->destinations->map(fn($d) => [
                    'id'                => $d->id,
                    'attraction_name'   => $d->attraction?->name ?? 'Other',
                    'other_destination' => $d->other_destination,
                ]),
                'current_profile'  => $visitor->profile ? [
                    'municipality'    => $visitor->profile->municipality,
                    'province'        => $visitor->profile->province,
                    'place_of_origin' => $visitor->profile->place_of_origin,
                    'contact_number'  => $visitor->profile->contact_number,
                    'middle_name'     => $visitor->profile->middle_name,
                ] : null,
                'purpose'          => $visitor->purpose === 'Other' && $visitor->purpose_other
                                         ? "Other: {$visitor->purpose_other}"
                                         : $visitor->purpose,
                'duration'         => $visitor->duration_of_stay,
                'fee_status'       => $visitor->fee_status,
                'arrival_at'       => $visitor->arrival_at->format('M d, Y h:i A'),
                'registered_by'    => $visitor->registeredBy->name ?? 'N/A',
                'receipt'          => $visitor->receipt,
            ],
        ]);
    }

    // ── Search visitor profiles ───────────────────────────────────────────────
    public function searchProfile(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2']);
        $q = $request->query('query');

        $profiles = VisitorProfile::where('contact_number', 'like', "%{$q}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$q}%"])
            ->with('latestVisit')
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'full_name'       => $p->full_name,
                'contact_number'  => $p->contact_number,
                'place_of_origin' => $p->place_of_origin,
                'municipality'    => $p->municipality,
                'province'        => $p->province,
                'visit_count'     => $p->visits()->count(),
                'last_visit'      => $p->latestVisit
                    ? $p->latestVisit->arrival_at->format('M d, Y')
                    : null,
            ]);

        return response()->json($profiles);
    }
}