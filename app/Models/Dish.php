<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dish extends Model {
    use HasFactory;
    protected $fillable = ['category_id','name','description','price','image','available','featured','preparation_time','calories','allergens'];
    protected $casts = ['available'=>'boolean','featured'=>'boolean'];
    public function category() { return $this->belongsTo(Category::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
}
