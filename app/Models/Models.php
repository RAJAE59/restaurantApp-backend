<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// ═══ Category ═══════════════════════════════════════
class Category extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description', 'image', 'sort_order', 'active'];
    public function dishes() { return $this->hasMany(Dish::class); }
}

// ═══ Dish ════════════════════════════════════════════
class Dish extends Model {
    use HasFactory;
    protected $fillable = ['category_id','name','description','price','image','available','featured','preparation_time','calories','allergens'];
    protected $casts    = ['available'=>'boolean','featured'=>'boolean','allergens'=>'array'];
    public function category() { return $this->belongsTo(Category::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
}

// ═══ Customer ════════════════════════════════════════
class Customer extends Model {
    use HasFactory;
    protected $fillable = ['name','email','phone','address','loyalty_points','notes'];
    public function orders() { return $this->hasMany(Order::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}

// ═══ Order ═══════════════════════════════════════════
class Order extends Model {
    use HasFactory;
    protected $fillable = ['customer_id','order_number','table_number','type','status','payment_status','total','notes'];
    public function customer()  { return $this->belongsTo(Customer::class); }
    public function items()     { return $this->hasMany(OrderItem::class); }
    public function payment()   { return $this->hasOne(Payment::class); }
}

// ═══ OrderItem ═══════════════════════════════════════
class OrderItem extends Model {
    use HasFactory;
    protected $fillable = ['order_id','dish_id','quantity','price','subtotal','notes'];
    public function dish()  { return $this->belongsTo(Dish::class); }
    public function order() { return $this->belongsTo(Order::class); }
}

// ═══ Reservation ═════════════════════════════════════
class Reservation extends Model {
    use HasFactory;
    protected $fillable = ['customer_id','name','email','phone','reservation_date','reservation_time','guests','table_number','status','special_requests'];
    protected $casts = ['reservation_date' => 'date'];
    public function customer() { return $this->belongsTo(Customer::class); }
}

// ═══ Payment ═════════════════════════════════════════
class Payment extends Model {
    use HasFactory;
    protected $fillable = ['order_id','payment_intent_id','amount','method','status'];
    public function order() { return $this->belongsTo(Order::class); }
}
