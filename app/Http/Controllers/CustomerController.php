<?php
// ════════════════════════════════════════════
// app/Http/Controllers/CustomerController.php
// ════════════════════════════════════════════
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller {
    public function index(Request $request) {
        $q = Customer::query();
        if ($request->search) $q->where('name','like','%'.$request->search.'%')->orWhere('email','like','%'.$request->search.'%')->orWhere('phone','like','%'.$request->search.'%');
        $customers = $q->orderByDesc('created_at')->paginate(15);
return response()->json($customers);
    }
    public function show(Customer $customer) { return response()->json($customer); }
    public function store(Request $request) {
        $data = $request->validate(['name'=>'required','email'=>'nullable|email|unique:customers','phone'=>'nullable','address'=>'nullable','notes'=>'nullable']);
        return response()->json(Customer::create($data), 201);
    }
    public function update(Request $request, Customer $customer) {
        $data = $request->validate(['name'=>'sometimes','email'=>'nullable|email|unique:customers,email,'.$customer->id,'phone'=>'nullable','address'=>'nullable','notes'=>'nullable']);
        $customer->update($data);
        return response()->json($customer);
    }
    public function destroy(Customer $customer) { $customer->delete(); return response()->json(['message'=>'Supprimé']); }
    public function orders(Customer $customer) { return response()->json($customer->orders()->with('items.dish')->orderByDesc('created_at')->get()); }
}
