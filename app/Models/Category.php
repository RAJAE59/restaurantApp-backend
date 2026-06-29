<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description', 'image', 'sort_order', 'active'];
    public function dishes() { return $this->hasMany(Dish::class); }
}
