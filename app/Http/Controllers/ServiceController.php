<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = DB::table('services')
            ->selectRaw('services.*, (SELECT COUNT(*) FROM employees WHERE employees.service_id = services.id) as employees_count')
            ->orderBy('name')
            ->get();
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
        ]);

        $id = DB::table('services')->insertGetId([
            'name'        => $request->name,
            'description' => $request->description,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(DB::table('services')->find($id), 201);
    }

    public function update(Request $request, $id)
    {
        DB::table('services')->where('id', $id)->update([
            'name'        => $request->name,
            'description' => $request->description,
            'updated_at'  => now(),
        ]);
        return response()->json(DB::table('services')->find($id));
    }

    public function destroy($id)
    {
        DB::table('services')->where('id', $id)->delete();
        return response()->json(['message' => 'Service supprimé']);
    }
}
