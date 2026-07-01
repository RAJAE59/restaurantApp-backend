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

        $stripeSecret = env('STRIPE_SECRET');

        if (!$stripeSecret) {
            return response()->json(['message' => 'Clé Stripe non configurée. Configurez STRIPE_SECRET dans les variables d\'environnement.'], 500);
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);

            $intent = \Stripe\PaymentIntent::create([
                'amount'   => (int)($order->total * 100),
                'currency' => 'mad',
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur Stripe: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirm a payment
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'order_id'          => 'required|exists:orders,id',
            'payment_intent_id' => 'nullable|string',
            'method'            => 'nullable|in:card,cash,transfer',
        ]);

        $order = Order::findOrFail($request->order_id);

        Payment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $request->payment_intent_id ?? 'manual_' . time(),
            'amount'            => $order->total,
            'method'            => $request->method ?? 'cash',
            'status'            => 'completed',
        ]);

        $order->update([
            'payment_status' => 'paid',
            'status'         => 'confirmed',
        ]);

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