<?php
// ════════════════════════════════════════════
// app/Http/Controllers/DishController.php
// ════════════════════════════════════════════
namespace App\Http\Controllers;
use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller {
    public function index(Request $request) {
        $q = Dish::with('category');
        if ($request->search)      $q->where('name','like','%'.$request->search.'%');
        if ($request->category_id) $q->where('category_id',$request->category_id);
        return response()->json($q->paginate($request->per_page ?? 20));
    }
    public function public(Request $request) {
        $q = Dish::with('category')->where('available',true);
        if ($request->category_id) $q->where('category_id',$request->category_id);
        return response()->json($q->get());
    }
    public function show($id) { return response()->json(Dish::with('category')->findOrFail($id)); }
    public function store(Request $request) {
        $data = $request->validate(['category_id'=>'required|exists:categories,id','name'=>'required','description'=>'nullable','price'=>'required|numeric','image'=>'nullable|url','available'=>'boolean','featured'=>'boolean','preparation_time'=>'integer','calories'=>'nullable|integer','allergens'=>'nullable|array']);
        return response()->json(Dish::create($data), 201);
    }
    public function update(Request $request, Dish $dish) {
        $data = $request->validate(['category_id'=>'sometimes|exists:categories,id','name'=>'sometimes','description'=>'nullable','price'=>'sometimes|numeric','image'=>'nullable|url','available'=>'boolean','featured'=>'boolean','preparation_time'=>'integer','calories'=>'nullable|integer','allergens'=>'nullable|array']);
        $dish->update($data);
        return response()->json($dish->load('category'));
    }
    public function destroy(Dish $dish) { $dish->delete(); return response()->json(['message'=>'Supprimé']); }
    public function toggleAvailability($id) {
        $dish = Dish::findOrFail($id);
        $dish->update(['available' => !$dish->available]);
        return response()->json($dish);
    }
}
