<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HubspotController;
use App\Http\Controllers\HubspotSnapshotController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hubspot/redirect', [HubspotController::class, 'redirectToHubspot']);
Route::get('/hubspot/callback', [HubspotController::class, 'callback']);

Route::post('/hubspot/import', [HubspotController::class, 'importContacts']);
Route::post('/hubspot/contact', [HubspotController::class, 'createContact']);
Route::post('/hubspot/deal', [HubspotController::class, 'createDeal']);
Route::get('/hubspot/status', [HubspotController::class, 'status']);
Route::get('/hubspot/overview', [HubspotController::class, 'overview']);

Route::post('/hubspot/snapshot', [HubspotSnapshotController::class, 'store']);
Route::get('/hubspot/history', [HubspotSnapshotController::class, 'history']);




Route::post('/hubspot/disconnect', [HubspotController::class, 'disconnect']);
