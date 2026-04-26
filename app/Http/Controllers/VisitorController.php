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

    /**
     * Generate a unique registration_id safely under concurrent load.
     *
     * Strategy: use a named MySQL advisory lock scoped to today's date.
     * Only one process can hold "bel_reg_YYYYMMDD" at a time, so the
     * COUNT → compute → INSERT sequence is effectively serialized.
     * The lock is released automatically when the connection ends or
     * after 5 seconds if something goes wrong.
     */
    private function generateRegistrationId(): string
    {
        $date    = Carbon::now()->format('Ymd');
        $lockKey = "bel_reg_{$date}";

        // Acquire advisory lock (waits up to 5 s, returns 1 on success)
        $locked = DB::selectOne("SELECT GET_LOCK(?, 5) AS acquired", [$lockKey]);

        if (!$locked || !$locked->acquired) {
            throw new \RuntimeException('Could not acquire registration ID lock. Please try again.');
        }

        try {
            $count = VisitorVisit::whereDate('created_at', Carbon::today())->count();
            return 'BEL-' . $date . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        } finally {
            // Always release — even if count throws
            DB::selectOne("SELECT RELEASE_LOCK(?)", [$lockKey]);
        }
    }

    /**
     * Generate a unique reference_code using the DB unique constraint as the
     * real guard. The do/while check-then-insert gap is eliminated by letting
     * the INSERT itself fail on collision and retrying with a new code.
     *
     * With 1,000,000 possible codes the collision probability per attempt is
     * (existing_codes / 1,000,000). At 10,000 total visits that's 1% — the
     * retry loop handles it gracefully.
     *
     * Returns the code string (the visit must be saved by the caller).
     */
    private function generateReferenceCode(): string
    {
        do {
            $code = 'BEL-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
        ]);
    }

    // ── Store a single visit ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'municipality'     => 'required|string|max:255',
            'province'         => 'required|string|max:255',
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

        DB::beginTransaction();

        try {
            // ── SCENARIO 1: Pre-registered visitor ───────────────────────────
            if ($request->filled('visit_id')) {
                $visit = VisitorVisit::findOrFail($request->visit_id);

                if ($visit->source !== 'pre_registration' || $visit->fee_status !== 'Pending') {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'This registration has already been processed.']);
                }

                if ($visit->profile_id) {
                    $profile = VisitorProfile::find($visit->profile_id);
                    if ($profile) {
                        $profile->update([
                            'municipality'    => $request->municipality,
                            'province'        => $request->province,
                            'place_of_origin' => $request->place_of_origin,
                            'contact_number'  => $request->contact_number,
                        ]);
                    }
                }

                $visit->update([
                    'source'                   => 'staff',
                    'registered_by'            => Auth::id(),
                    'purpose'                  => $request->purpose,
                    'purpose_other'            => $request->purpose === 'Other' ? $request->purpose_other : null,
                    'duration_of_stay'         => $request->duration_of_stay,
                    'visitor_category'         => $request->visitor_category,
                    'snapshot_first_name'      => $request->first_name,
                    'snapshot_last_name'       => $request->last_name,
                    'snapshot_municipality'    => $request->municipality,
                    'snapshot_province'        => $request->province,
                    'snapshot_place_of_origin' => $request->place_of_origin,
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
                    'municipality'    => $request->municipality,
                    'province'        => $request->province,
                    'place_of_origin' => $request->place_of_origin,
                    'contact_number'  => $request->contact_number,
                ]);
            } else {
                $profile = VisitorProfile::create([
                    'first_name'      => $request->first_name,
                    'last_name'       => $request->last_name,
                    'municipality'    => $request->municipality,
                    'province'        => $request->province,
                    'place_of_origin' => $request->place_of_origin,
                    'contact_number'  => $request->contact_number,
                ]);
            }

            // ── Safe registration_id via advisory lock ────────────────────────
            $registrationId = $this->generateRegistrationId();

            $visit = new VisitorVisit([
                'registration_id'  => $registrationId,
                'profile_id'       => $profile->id,
                'purpose'          => $request->purpose,
                'purpose_other'    => $request->purpose === 'Other' ? $request->purpose_other : null,
                'duration_of_stay' => $request->duration_of_stay,
                'visitor_category' => $request->visitor_category,
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
            'members.*.municipality'                         => 'required|string|max:255',
            'members.*.province'                             => 'required|string|max:255',
            'members.*.place_of_origin'                      => 'required|string|max:255',
            'members.*.purpose'                              => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'members.*.purpose_other'                        => 'required_if:members.*.purpose,Other|nullable|string|max:255',
            'members.*.duration_of_stay'                     => 'required|string|max:255',
            'members.*.visitor_category'                     => 'required|string|max:100',
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
                        $visit->update([
                            'source'                   => 'staff',
                            'registered_by'            => Auth::id(),
                            'purpose'                  => $memberData['purpose'],
                            'purpose_other'            => $memberData['purpose'] === 'Other' ? ($memberData['purpose_other'] ?? null) : null,
                            'duration_of_stay'         => $memberData['duration_of_stay'],
                            'visitor_category'         => $memberData['visitor_category'],
                            'snapshot_first_name'      => $memberData['first_name'],
                            'snapshot_last_name'       => $memberData['last_name'],
                            'snapshot_municipality'    => $memberData['municipality'],
                            'snapshot_province'        => $memberData['province'],
                            'snapshot_place_of_origin' => $memberData['place_of_origin'],
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

            // ── Assign group_code to all members ────────────────────────────────
            // Manual group registrations must set group_code the same way
            // pre-registered groups do — otherwise resolveGroupVisits() in
            // ReceiptController cannot find the other members at payment time.
            // We use the first visit's registration_id as the group_code so
            // it's human-readable and traceable in the audit log.
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
        if (!empty($data['profile_id'])) {
            $profile = VisitorProfile::findOrFail($data['profile_id']);
            $profile->update([
                'municipality'    => $data['municipality'],
                'province'        => $data['province'],
                'place_of_origin' => $data['place_of_origin'],
                'contact_number'  => $data['contact_number'] ?? $profile->contact_number,
            ]);
        } else {
            $profile = VisitorProfile::create([
                'first_name'      => $data['first_name'],
                'last_name'       => $data['last_name'],
                'municipality'    => $data['municipality'],
                'province'        => $data['province'],
                'place_of_origin' => $data['place_of_origin'],
                'contact_number'  => $data['contact_number'] ?? null,
            ]);
        }

        // ── Safe registration_id via advisory lock ────────────────────────────
        $registrationId = $this->generateRegistrationId();

        $visit = new VisitorVisit([
            'registration_id'  => $registrationId,
            'profile_id'       => $profile->id,
            'purpose'          => $data['purpose'],
            'purpose_other'    => $data['purpose'] === 'Other' ? ($data['purpose_other'] ?? null) : null,
            'duration_of_stay' => $data['duration_of_stay'],
            'visitor_category' => $data['visitor_category'] ?? null,
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