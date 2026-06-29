<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model {
    use HasFactory;
    protected $fillable = ['customer_id','name','email','phone','reservation_date','reservation_time','guests','table_number','status','special_requests'];
    protected $casts = ['reservation_date' => 'date'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
