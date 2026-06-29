<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'revenue_today'        => Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total'),
            'revenue_month'        => Order::where('created_at', '>=', $thisMonth)->where('status', 'completed')->sum('total'),
            'orders_today'         => Order::whereDate('created_at', $today)->count(),
            'orders_pending'       => Order::where('status', 'pending')->count(),
            'customers_total'      => Customer::count(),
            'customers_new_month'  => Customer::where('created_at', '>=', $thisMonth)->count(),
            'reservations_today'   => Reservation::whereDate('reservation_date', $today)->count(),
            'reservations_pending' => Reservation::where('status', 'pending')->count(),
            'dishes_total'         => Dish::count(),
            'dishes_unavailable'   => Dish::where('available', false)->count(),
        ]);
    }

    public function revenueChart()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date'    => $date->format('d/m'),
                'revenue' => Order::whereDate('created_at', $date)
                                  ->where('status', 'completed')
                                  ->sum('total'),
                'orders'  => Order::whereDate('created_at', $date)->count(),
            ];
        }
        return response()->json($data);
    }

    public function topDishes()
    {
        $dishes = DB::table('order_items')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->select('dishes.id', 'dishes.name', 'dishes.price', 'dishes.image',
                     DB::raw('SUM(order_items.quantity) as total_sold'),
                     DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'))
            ->groupBy('dishes.id', 'dishes.name', 'dishes.price', 'dishes.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json($dishes);
    }

    public function recentOrders()
    {
        $orders = Order::with(['customer', 'items.dish'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json($orders);
    }
}
