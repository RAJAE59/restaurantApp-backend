<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model {
    use HasFactory;
    protected $fillable = ['name','email','phone','address','loyalty_points','notes'];
    public function orders() { return $this->hasMany(Order::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}
