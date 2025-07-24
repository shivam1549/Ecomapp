<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
    {
        return Currency::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:currencies,code',
            'symbol' => 'required|string',
            'exchange_rate' => 'required|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        return Currency::create($validated);
    }

    public function show(Currency $currency)
    {
        return $currency;
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:currencies,code,' . $currency->id,
            'symbol' => 'sometimes|string',
            'exchange_rate' => 'sometimes|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($validated);
        return $currency;
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return response()->json(['error' => 'Cannot delete default currency'], 400);
        }

        $currency->delete();
        return response()->json(['success' => true]);
    }
}
