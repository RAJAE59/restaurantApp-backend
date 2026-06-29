<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model {
    use HasFactory;
    protected $fillable = ['customer_id','order_number','table_number','type','status','payment_status','total','notes'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
}
