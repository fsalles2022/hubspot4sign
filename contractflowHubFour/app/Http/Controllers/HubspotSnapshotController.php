<?php

namespace App\Http\Controllers;

use App\Services\HubspotService;
use App\Services\HubspotSnapshotService;
use Illuminate\Pipeline\Hub;

class HubspotSnapshotController extends Controller
{
    public function __construct(
        protected HubspotService $hubspotService,
        protected HubspotSnapshotService $hubspotSnapshotService
    ) {}


    public function store(
        HubspotService $hubspot,
        HubspotSnapshotService $snapshotService
    ) {
        $overview = $hubspot->getAccountOverview();

        if (!$overview) {
            return response()->json([
                'error' => 'HubSpot não conectado'
            ], 401);
        }

        $snapshot = $snapshotService->createFromOverview($overview);

        return response()->json($snapshot);
    }


    public function status()
    {
        if (!$this->hubspotService->hasValidToken()) {
            return response()->json([
                'connected' => false
            ]);
        }

        return response()->json([
            'connected' => true,
            'account' => [
                'portal_id' => $this->hubspotService->getPortalId()
            ]
        ]);
    }


    public function history(HubspotSnapshotService $snapshotService)
    {
        $overview = $this->hubspotService->getAccountOverview();
        $portalId = $overview['portal_id'] ?? null;

        if (!$portalId) {
            return response()->json(['error' => 'Portal ID not found'], 404);
        }

        $history = $snapshotService->getHistory($portalId, 30);

        return response()->json($history);
    }

    public function overview()
    {
        return response()->json(
            $this->hubspotService->getAccountOverview()
        );
    }
}
