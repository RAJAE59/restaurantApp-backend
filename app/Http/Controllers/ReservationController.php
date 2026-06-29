<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $q = Reservation::query();
        if ($request->status) $q->where('status', $request->status);
        if ($request->date)   $q->whereDate('reservation_date', $request->date);
        return response()->json($q->orderBy('reservation_date')->orderBy('reservation_time')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email',
            'phone'            => 'required|string|max:20',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'guests'           => 'required|integer|min:1|max:50',
            'table_number'     => 'nullable|integer',
            'special_requests' => 'nullable|string',
        ]);
        $data['status'] = 'pending';
        return response()->json(Reservation::create($data), 201);
    }

    public function show(Reservation $reservation) { return response()->json($reservation); }

    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'name'             => 'sometimes|string',
            'phone'            => 'sometimes|string',
            'reservation_date' => 'sometimes|date',
            'reservation_time' => 'sometimes',
            'guests'           => 'sometimes|integer',
            'table_number'     => 'nullable|integer',
            'special_requests' => 'nullable|string',
        ]);
        $reservation->update($data);
        return response()->json($reservation);
    }

    public function destroy(Reservation $reservation) { $reservation->delete(); return response()->json(['message'=>'Supprimé']); }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,cancelled,completed']);
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $request->status]);
        return response()->json($reservation);
    }

    public function calendar(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        return response()->json(Reservation::whereDate('reservation_date', $date)->orderBy('reservation_time')->get());
    }
}
