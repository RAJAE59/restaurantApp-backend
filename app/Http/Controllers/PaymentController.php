<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a Stripe PaymentIntent (sandbox)
     */
    public function createIntent(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Stripe PHP SDK call (sandbox)
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $intent = \Stripe\PaymentIntent::create([
            'amount'   => (int)($order->total * 100), // centimes
            'currency' => 'mad', // Dirham Marocain
            'metadata' => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'amount'        => $order->total,
            'order_number'  => $order->order_number,
        ]);
    }

    /**
     * Confirm a payment after Stripe confirms it
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'order_id'          => 'required|exists:orders,id',
            'payment_intent_id' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        Payment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $request->payment_intent_id,
            'amount'            => $order->total,
            'method'            => $request->method ?? 'card',
            'status'            => 'completed',
        ]);

        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);

        return response()->json([
            'message' => 'Paiement confirmé avec succès',
            'order'   => $order,
        ]);
    }

    /**
     * Payment history
     */
    public function history(Request $request)
    {
        $payments = Payment::with('order')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payments);
    }
}
