<?php
// ════════════════════════════════════════
// app/Http/Controllers/CategoryController.php
// ════════════════════════════════════════
namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    public function index()  { return response()->json(Category::orderBy('sort_order')->paginate(50)); }
    public function public() { return response()->json(Category::where('active',true)->orderBy('sort_order')->get()); }
    public function store(Request $request) {
        $data = $request->validate(['name'=>'required|string','description'=>'nullable','image'=>'nullable|url','sort_order'=>'integer','active'=>'boolean']);
        return response()->json(Category::create($data), 201);
    }
    public function update(Request $request, Category $category) {
        $data = $request->validate(['name'=>'sometimes|string','description'=>'nullable','image'=>'nullable|url','sort_order'=>'integer','active'=>'boolean']);
        $category->update($data);
        return response()->json($category);
    }
    public function destroy(Category $category) { $category->delete(); return response()->json(['message'=>'Supprimé']); }
    public function show(Category $category)    { return response()->json($category); }
}
