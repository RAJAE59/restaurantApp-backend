<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = DB::table('roles')
            ->selectRaw('roles.*, (SELECT COUNT(*) FROM employees WHERE employees.role_id = roles.id) as employees_count')
            ->orderBy('id')
            ->get()
            ->map(function($r) {
                $r->permissions = json_decode($r->permissions ?? '[]');
                return $r;
            });
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|unique:roles',
            'display_name' => 'required|string',
            'description'  => 'nullable|string',
            'color'        => 'nullable|string',
            'permissions'  => 'nullable|array',
        ]);

        $id = DB::table('roles')->insertGetId([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'color'        => $request->color ?? '#C9A84C',
            'permissions'  => json_encode($request->permissions ?? []),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(DB::table('roles')->find($id), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'display_name' => 'sometimes|string',
            'description'  => 'nullable|string',
            'color'        => 'nullable|string',
            'permissions'  => 'nullable|array',
        ]);

        DB::table('roles')->where('id', $id)->update([
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'color'        => $request->color,
            'permissions'  => json_encode($request->permissions ?? []),
            'updated_at'   => now(),
        ]);

        return response()->json(DB::table('roles')->find($id));
    }

    public function destroy($id)
    {
        DB::table('roles')->where('id', $id)->delete();
        return response()->json(['message' => 'Rôle supprimé']);
    }
}
