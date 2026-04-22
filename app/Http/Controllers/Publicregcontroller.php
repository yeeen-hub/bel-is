<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorProfile;
use App\Models\FeeCategory;
use App\Models\BarangayAttraction;
use App\Traits\SavesVisitorDestinations;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class PublicRegController extends Controller
{
    use SavesVisitorDestinations;

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
        ]);
    }

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
            'visitor_category' => 'required|string|max:100',
            'destinations'                         => 'nullable|array',
            'destinations.*.attraction_id'         => 'nullable|integer|exists:barangay_attractions,id',
            'destinations.*.other_destination'     => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            [$visit, $referenceCode] = $this->createPreRegVisit(
                first_name:       $request->first_name,
                last_name:        $request->last_name,
                municipality:     $request->municipality,
                province:         $request->province,
                contact:          $request->contact_number,
                purpose:          $request->purpose,
                purposeOther:     $request->purpose_other,
                duration:         $request->duration_of_stay,
                visitor_category: $request->visitor_category,
                destinations:     $request->destinations ?? [],
            );

            DB::commit();

            return back()->with([
                'success'          => true,
                'mode'             => 'single',
                'reference_code'   => $referenceCode,
                'visit_id'         => $visit->id,
                'full_name'        => "{$request->first_name} {$request->last_name}",
                'visitor_category' => $request->visitor_category,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'members'                                        => 'required|array|min:2|max:20',
            'members.*.first_name'                           => 'required|string|max:255',
            'members.*.last_name'                            => 'required|string|max:255',
            'members.*.municipality'                         => 'required|string|max:255',
            'members.*.province'                             => 'required|string|max:255',
            'members.*.purpose'                              => 'required|in:Tourism,Research,Event,Official Visit,Other',
            'members.*.purpose_other'                        => 'nullable|string|max:255',
            'members.*.duration_of_stay'                     => 'required|string|max:255',
            'members.*.contact_number'                       => 'nullable|string|max:20',
            'members.*.visitor_category'                     => 'required|string|max:100',
            'members.*.destinations'                         => 'nullable|array',
            'members.*.destinations.*.attraction_id'         => 'nullable|integer|exists:barangay_attractions,id',
            'members.*.destinations.*.other_destination'     => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $results = [];

            foreach ($request->members as $index => $member) {
                [$visit, $referenceCode] = $this->createPreRegVisit(
                    first_name:       $member['first_name'],
                    last_name:        $member['last_name'],
                    municipality:     $member['municipality'],
                    province:         $member['province'],
                    contact:          $member['contact_number'] ?? null,
                    purpose:          $member['purpose'],
                    purposeOther:     $member['purpose_other'] ?? null,
                    duration:         $member['duration_of_stay'],
                    visitor_category: $member['visitor_category'],
                    destinations:     $member['destinations'] ?? [],
                );

                $results[] = [
                    'visit_id'         => $visit->id,
                    'full_name'        => "{$member['first_name']} {$member['last_name']}",
                    'reference_code'   => $referenceCode,
                    'visitor_category' => $member['visitor_category'],
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

    private function formatVisit(VisitorVisit $visit): array
    {
        $visit->load('destinations.attraction');

        return [
            'visit_id'         => $visit->id,
            'reference_code'   => $visit->reference_code,
            'registration_id'  => $visit->registration_id,
            'first_name'       => $visit->snapshot_first_name,
            'last_name'        => $visit->snapshot_last_name,
            'municipality'     => $visit->snapshot_municipality,
            'province'         => $visit->snapshot_province,
            'place_of_origin'  => $visit->snapshot_place_of_origin,
            'contact_number'   => $visit->snapshot_contact_number,
            'purpose'          => $visit->purpose,
            'purpose_other'    => $visit->purpose_other,
            'duration_of_stay' => $visit->duration_of_stay,
            'visitor_category' => $visit->visitor_category,
            // Return existing destination selections so form can pre-fill
            'destinations'     => $visit->destinations->map(fn($d) => [
                'attraction_id'     => $d->attraction_id,
                'other_destination' => $d->other_destination,
            ])->values()->toArray(),
            'fee_status'       => $visit->fee_status,
            'is_leader'        => $visit->group_code === $visit->reference_code,
            'created_at'       => Carbon::parse($visit->created_at)->format('M d, Y h:i A'),
        ];
    }

    private function createPreRegVisit(
        string  $first_name,
        string  $last_name,
        string  $municipality,
        string  $province,
        ?string $contact,
        string  $purpose,
        ?string $purposeOther,
        string  $duration,
        ?string $visitor_category = null,
        array   $destinations = [],
    ): array {
        $profile = VisitorProfile::create([
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'municipality'    => $municipality,
            'province'        => $province,
            'place_of_origin' => "{$municipality}, {$province}",
            'contact_number'  => $contact,
        ]);

        $registrationId = 'BEL-' . Carbon::now()->format('Ymd') . '-' . str_pad(
            VisitorVisit::whereDate('created_at', Carbon::today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

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
            'visitor_category' => $visitor_category,
            'fee_status'       => 'Pending',
            'source'           => 'pre_registration',
            'registered_by'    => null,
        ]);

        $visit->takeSnapshot($profile);
        $visit->save();

        $this->saveDestinations($visit->id, $destinations);

        return [$visit, $referenceCode];
    }
}