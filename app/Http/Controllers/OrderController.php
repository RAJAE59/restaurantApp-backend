<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;  // ← Cette ligne doit être présente


class OrderController extends Controller
{
public function index(Request $request)
{
    $orders = \App\Models\Order::with(['customer', 'items.dish'])
        ->orderByDesc('created_at')
        ->paginate(15);
    
    return response()->json($orders);
}

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'table_number'=> 'nullable|integer',
            'type'        => 'required|in:Sur place,A emporter,Livraison',
            'items'       => 'required|array|min:1',
            'items.*.dish_id'  => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes'       => 'nullable|string',
        ]);

        $total = 0;
        $items = [];

        foreach ($request->items as $item) {
            $dish = \App\Models\Dish::findOrFail($item['dish_id']);
            $subtotal = $dish->price * $item['quantity'];
            $total += $subtotal;
            $items[] = [
                'dish_id'  => $dish->id,
                'quantity' => $item['quantity'],
                'price'    => $dish->price,
                'subtotal' => $subtotal,
                'notes'    => $item['notes'] ?? null,
            ];
        }

        $order = Order::create([
            'customer_id'  => $request->customer_id,
            'table_number' => $request->table_number,
            'type'         => $request->type,
            'status'       => 'pending',
            'total'        => $total,
            'notes'        => $request->notes,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
        ]);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        return response()->json($order->load('items.dish'), 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['customer', 'items.dish']));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'table_number' => 'nullable|integer',
            'notes'        => 'nullable|string',
        ]);

        $order->update($request->only('table_number', 'notes'));
        return response()->json($order->load('items.dish'));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Commande supprimée']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($order->load('items.dish'));
    }

    public function todayStats()
    {
        $today = Carbon::today();
        return response()->json([
            'total'     => Order::whereDate('created_at', $today)->count(),
            'pending'   => Order::whereDate('created_at', $today)->where('status', 'pending')->count(),
            'preparing' => Order::whereDate('created_at', $today)->where('status', 'preparing')->count(),
            'completed' => Order::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'revenue'   => Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total'),
        ]);
    }
}
