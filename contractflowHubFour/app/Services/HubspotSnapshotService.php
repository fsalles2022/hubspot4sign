<?php

namespace App\Services;

use App\Models\HubspotSnapshot;
use Carbon\Carbon;

class HubspotSnapshotService
{
    public function __construct(
        protected HubspotService $hubspotService
    ) {}

    public function createFromOverview(?array $overview)
    {
        if (!$overview) {
            return null;
        }

        return HubspotSnapshot::updateOrCreate(
            [
                'portal_id' => $overview['portal_id'],
                'snapshot_date' => now()->toDateString(),
            ],
            [
                'company_name' => $overview['company_name'] ?? null,
                'region' => $overview['region'] ?? null,
                'timezone' => $overview['timezone'] ?? null,
                'contacts' => $overview['objects']['contacts'] ?? 0,
                'companies' => $overview['objects']['companies'] ?? 0,
                'deals' => $overview['objects']['deals'] ?? 0,
            ]
        );
    }


    public function getHistory(int $portalId, int $days = 30)
    {
        return HubspotSnapshot::where('portal_id', $portalId)
            ->orderBy('snapshot_date')
            ->take($days)
            ->get();
    }
}
