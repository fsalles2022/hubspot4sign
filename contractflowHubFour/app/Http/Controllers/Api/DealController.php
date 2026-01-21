<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        return Deal::with(['client', 'company'])
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->company_id, fn ($q) =>
                $q->where('company_id', $request->company_id)
            )
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'value' => 'required|numeric',
            'status' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        return Deal::create($data);
    }

    public function show(Deal $deal)
    {
        return $deal->load(['client', 'company']);
    }

    public function update(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'value' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $deal->update($data);

        return $deal->refresh();
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return response()->json(['message' => 'Negócio removido com sucesso']);
    }
}
