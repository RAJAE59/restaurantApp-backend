<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;

// ── Public routes ──────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Public menu
Route::get('/menu/categories',  [CategoryController::class, 'public']);
Route::get('/menu/dishes',      [DishController::class, 'public']);
Route::get('/menu/dishes/{id}', [DishController::class, 'show']);

// ── Protected routes ───────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Users
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::put('/me',      [AuthController::class, 'updateProfile']);
    Route::get('/users',   function () {
        return response()->json(\App\Models\User::select('id','name','email','role','created_at')->get());
    });

    // Dashboard
    Route::get('/dashboard/stats',         [DashboardController::class, 'stats']);
    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'revenueChart']);
    Route::get('/dashboard/top-dishes',    [DashboardController::class, 'topDishes']);
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Dishes
    Route::post('/dishes/{id}/toggle-availability', [DishController::class, 'toggleAvailability']);
    Route::apiResource('dishes', DishController::class);

    // Orders
    Route::get('/orders/stats/today',  [OrderController::class, 'todayStats']);
    Route::put('/orders/{id}/status',  [OrderController::class, 'updateStatus']);
    Route::apiResource('orders', OrderController::class);

    // Customers
    Route::get('/customers/{id}/orders', [CustomerController::class, 'orders']);
    Route::apiResource('customers', CustomerController::class);

    // Reservations
    Route::get('/reservations/calendar/view',  [ReservationController::class, 'calendar']);
    Route::put('/reservations/{id}/status',    [ReservationController::class, 'updateStatus']);
    Route::apiResource('reservations', ReservationController::class);

    // Payments
    Route::post('/payments/intent',  [PaymentController::class, 'createIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirm']);
    Route::get('/payments/history',  [PaymentController::class, 'history']);

    // Roles
    Route::get('/roles',         [RoleController::class, 'index']);
    Route::post('/roles',        [RoleController::class, 'store']);
    Route::put('/roles/{id}',    [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // Services
    Route::get('/services',         [ServiceController::class, 'index']);
    Route::post('/services',        [ServiceController::class, 'store']);
    Route::put('/services/{id}',    [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    // Employees
    Route::get('/employees/stats',   [EmployeeController::class, 'stats']);
    Route::get('/employees',         [EmployeeController::class, 'index']);
    Route::post('/employees',        [EmployeeController::class, 'store']);
    Route::get('/employees/{id}',    [EmployeeController::class, 'show']);
    Route::put('/employees/{id}',    [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // Salaries
    Route::get('/salaries/month-stats',       [SalaryController::class, 'monthStats']);
    Route::post('/salaries/generate-monthly', [SalaryController::class, 'generateMonthly']);
    Route::get('/salaries',                   [SalaryController::class, 'index']);
    Route::post('/salaries',                  [SalaryController::class, 'store']);
    Route::put('/salaries/{id}/pay',          [SalaryController::class, 'pay']);
    Route::delete('/salaries/{id}',           [SalaryController::class, 'destroy']);
});