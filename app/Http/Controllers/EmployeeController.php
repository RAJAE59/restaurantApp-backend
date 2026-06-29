<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('employees')
            ->leftJoin('roles',    'employees.role_id',    '=', 'roles.id')
            ->leftJoin('services', 'employees.service_id', '=', 'services.id')
            ->select(
                'employees.*',
                'roles.display_name as role_name',
                'roles.color as role_color',
                'services.name as service_name'
            );

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('employees.first_name', 'like', '%'.$request->search.'%')
                  ->orWhere('employees.last_name',  'like', '%'.$request->search.'%')
                  ->orWhere('employees.email',       'like', '%'.$request->search.'%');
            });
        }

        if ($request->service_id) $query->where('employees.service_id', $request->service_id);
        if ($request->status)     $query->where('employees.status', $request->status);

        $employees = $query->orderByDesc('employees.created_at')->paginate(15);
        return response()->json($employees);
    }

    public function show($id)
    {
        $employee = DB::table('employees')
            ->leftJoin('roles',    'employees.role_id',    '=', 'roles.id')
            ->leftJoin('services', 'employees.service_id', '=', 'services.id')
            ->select('employees.*', 'roles.display_name as role_name', 'services.name as service_name')
            ->where('employees.id', $id)
            ->first();

        if (!$employee) return response()->json(['message' => 'Employé non trouvé'], 404);

        // Salaires récents
        $employee->salaries = DB::table('salaries')
            ->where('employee_id', $id)
            ->orderByDesc('year')->orderByDesc('month')
            ->limit(12)->get();

        return response()->json($employee);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'email'         => 'nullable|email|unique:employees',
            'phone'         => 'nullable|string',
            'role_id'       => 'nullable|exists:roles,id',
            'service_id'    => 'nullable|exists:services,id',
            'position'      => 'nullable|string',
            'contract_type' => 'in:cdi,cdd,parttime,intern',
            'base_salary'   => 'nullable|numeric',
            'hire_date'     => 'nullable|date',
            'birth_date'    => 'nullable|date',
            'cin'           => 'nullable|string',
        ]);

        $id = DB::table('employees')->insertGetId([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'role_id'       => $request->role_id,
            'service_id'    => $request->service_id,
            'position'      => $request->position,
            'contract_type' => $request->contract_type ?? 'cdi',
            'base_salary'   => $request->base_salary ?? 0,
            'hire_date'     => $request->hire_date,
            'birth_date'    => $request->birth_date,
            'cin'           => $request->cin,
            'status'        => 'active',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(DB::table('employees')->find($id), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name'    => 'sometimes|string',
            'last_name'     => 'sometimes|string',
            'email'         => 'nullable|email|unique:employees,email,'.$id,
            'phone'         => 'nullable|string',
            'role_id'       => 'nullable|exists:roles,id',
            'service_id'    => 'nullable|exists:services,id',
            'position'      => 'nullable|string',
            'contract_type' => 'in:cdi,cdd,parttime,intern',
            'base_salary'   => 'nullable|numeric',
            'status'        => 'in:active,inactive,suspended',
            'hire_date'     => 'nullable|date',
            'cin'           => 'nullable|string',
        ]);

        DB::table('employees')->where('id', $id)->update(array_merge(
            $request->only('first_name','last_name','email','phone','role_id','service_id','position','contract_type','base_salary','status','hire_date','birth_date','cin'),
            ['updated_at' => now()]
        ));

        return response()->json(DB::table('employees')->find($id));
    }

    public function destroy($id)
    {
        DB::table('employees')->where('id', $id)->delete();
        return response()->json(['message' => 'Employé supprimé']);
    }

    public function stats()
    {
        return response()->json([
            'total'      => DB::table('employees')->count(),
            'active'     => DB::table('employees')->where('status','active')->count(),
            'inactive'   => DB::table('employees')->where('status','inactive')->count(),
            'total_salary'=> DB::table('employees')->where('status','active')->sum('base_salary'),
            'by_service' => DB::table('employees')
                ->join('services','employees.service_id','=','services.id')
                ->selectRaw('services.name, COUNT(*) as count')
                ->groupBy('services.name')->get(),
        ]);
    }
}
