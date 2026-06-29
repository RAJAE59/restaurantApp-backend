<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response()->json(['message' => 'API RestaurantPro'], 200);
})->where('any', '.*');