<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all()); // Debugging line to check request data
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.sku' => 'required|string|max:255',
            'items.*.attributes' => 'nullable|array',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'shipping_charge' => 'nullable|numeric',
            'total' => 'required|numeric',
            'shipping_address' => 'required|string|max:255',
        ]);

        // Create Order
        $order = Order::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'order_number' => (string) Str::uuid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'currency_code' => 'INR', // or use from request if needed
            'currency_rate' => 1,
            'subtotal' => $validated['subtotal'],
            'discount_total' => $validated['discount'] ?? 0,
            'tax_total' => 0,
            'shipping_total' => $validated['shipping_charge'] ?? 0,
            'grand_total' => $validated['total'],
            'shipping_address' => json_encode(['address' => $validated['shipping_address']]), // or use full address object
        ]);
        // return response()->json(['message' => 'Order placed successfully', 'order_id' => $order->id], 201);
        // Save Order Items
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_variation_id' => $item['variation_id'] ?? null,
                'name' => $item['name'],
                // 'sku' => $item['sku'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
                'attributes' => $item['attributes'] ?? [],
            ]);
        }

        return response()->json(['message' => 'Order placed successfully', 'order_id' => $order->id], 201);
    }
}
