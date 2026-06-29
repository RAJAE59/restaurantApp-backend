<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model {
    use HasFactory;
    protected $fillable = ['order_id','dish_id','quantity','price','subtotal','notes'];
    public function dish() { return $this->belongsTo(Dish::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
