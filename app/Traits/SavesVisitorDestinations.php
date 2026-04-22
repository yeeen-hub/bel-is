<?php

namespace App\Traits;

use App\Models\VisitorDestination;
use App\Models\UnrecognizedAttraction;

trait SavesVisitorDestinations
{
    /**
     * Persist destination selections for a single visit.
     *
     * $destinations is the array sent from the frontend:
     *   [
     *     { "attraction_id": 3 },
     *     { "attraction_id": null, "other_destination": "Secret cove near sitio X" },
     *   ]
     *
     * Existing records for the visit are wiped then reinserted (idempotent).
     */
    protected function saveDestinations(string $visitId, array $destinations): void
    {
        // Remove old records first (safe for pre-reg confirm re-saves)
        VisitorDestination::where('visit_id', $visitId)->delete();

        foreach ($destinations as $dest) {
            $attractionId = !empty($dest['attraction_id']) ? (int) $dest['attraction_id'] : null;
            $otherText    = trim($dest['other_destination'] ?? '');

            // Skip completely empty entries
            if ($attractionId === null && $otherText === '') {
                continue;
            }

            VisitorDestination::create([
                'visit_id'          => $visitId,
                'attraction_id'     => $attractionId,
                'other_destination' => $attractionId === null ? $otherText : null,
            ]);

            // If the visitor typed a custom "Other" destination,
            // log it to unrecognized_attractions for admin notification.
            if ($attractionId === null && $otherText !== '') {
                UnrecognizedAttraction::create([
                    'visit_id'    => $visitId,
                    'name'        => $otherText,
                    'is_reviewed' => false,
                ]);
            }
        }
    }
}