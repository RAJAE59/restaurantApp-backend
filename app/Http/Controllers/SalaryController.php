<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('salaries')
            ->join('employees', 'salaries.employee_id', '=', 'employees.id')
            ->leftJoin('services', 'employees.service_id', '=', 'services.id')
            ->select(
                'salaries.*',
                'employees.first_name', 'employees.last_name',
                'employees.position',
                'services.name as service_name'
            );

        if ($request->month) $query->where('salaries.month', $request->month);
        if ($request->year)  $query->where('salaries.year',  $request->year);
        if ($request->employee_id) $query->where('salaries.employee_id', $request->employee_id);
        if ($request->status) $query->where('salaries.payment_status', $request->status);

        $salaries = $query->orderByDesc('salaries.year')
                          ->orderByDesc('salaries.month')
                          ->paginate(20);

        return response()->json($salaries);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020',
            'bonus'          => 'nullable|numeric',
            'deductions'     => 'nullable|numeric',
            'overtime_hours' => 'nullable|numeric',
            'notes'          => 'nullable|string',
        ]);

        $employee = DB::table('employees')->find($request->employee_id);
        $base     = $employee->base_salary;
        $overtime = ($request->overtime_hours ?? 0) * ($base / 26 / 8) * 1.25;
        $net      = $base + ($request->bonus ?? 0) + $overtime - ($request->deductions ?? 0);

        // Check if already exists
        $exists = DB::table('salaries')
            ->where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->where('year',  $request->year)
            ->first();

        if ($exists) {
            return response()->json(['message' => 'Salaire déjà généré pour ce mois'], 422);
        }

        $id = DB::table('salaries')->insertGetId([
            'employee_id'    => $request->employee_id,
            'month'          => $request->month,
            'year'           => $request->year,
            'base_salary'    => $base,
            'bonus'          => $request->bonus ?? 0,
            'deductions'     => $request->deductions ?? 0,
            'overtime_hours' => $request->overtime_hours ?? 0,
            'overtime_pay'   => $overtime,
            'net_salary'     => $net,
            'payment_status' => 'pending',
            'notes'          => $request->notes,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json(DB::table('salaries')->find($id), 201);
    }

    public function pay($id)
    {
        DB::table('salaries')->where('id', $id)->update([
            'payment_status' => 'paid',
            'payment_date'   => now()->toDateString(),
            'updated_at'     => now(),
        ]);
        return response()->json(['message' => 'Salaire marqué comme payé']);
    }

    public function destroy($id)
    {
        DB::table('salaries')->where('id', $id)->delete();
        return response()->json(['message' => 'Supprimé']);
    }

    public function generateMonthly(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer',
        ]);

        $employees = DB::table('employees')->where('status', 'active')->get();
        $generated = 0;
        $skipped   = 0;

        foreach ($employees as $emp) {
            $exists = DB::table('salaries')
                ->where('employee_id', $emp->id)
                ->where('month', $request->month)
                ->where('year',  $request->year)
                ->first();

            if ($exists) { $skipped++; continue; }

            DB::table('salaries')->insert([
                'employee_id'    => $emp->id,
                'month'          => $request->month,
                'year'           => $request->year,
                'base_salary'    => $emp->base_salary,
                'bonus'          => 0,
                'deductions'     => 0,
                'overtime_hours' => 0,
                'overtime_pay'   => 0,
                'net_salary'     => $emp->base_salary,
                'payment_status' => 'pending',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $generated++;
        }

        return response()->json([
            'message'   => "Génération terminée",
            'generated' => $generated,
            'skipped'   => $skipped,
        ]);
    }

    public function monthStats(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        return response()->json([
            'total_net'   => DB::table('salaries')->where('month',$month)->where('year',$year)->sum('net_salary'),
            'total_paid'  => DB::table('salaries')->where('month',$month)->where('year',$year)->where('payment_status','paid')->sum('net_salary'),
            'count'       => DB::table('salaries')->where('month',$month)->where('year',$year)->count(),
            'paid_count'  => DB::table('salaries')->where('month',$month)->where('year',$year)->where('payment_status','paid')->count(),
            'pending_count'=> DB::table('salaries')->where('month',$month)->where('year',$year)->where('payment_status','pending')->count(),
        ]);
    }
}
