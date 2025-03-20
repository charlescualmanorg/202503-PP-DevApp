<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverStatusController;
use App\Http\Controllers\RideController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::middleware(['auth'])->group(function () {
    // Página de selección para nuevo viaje
    Route::get('rides/new', 'RideController@new')->name('rides.new');
    // Formulario para crear solicitud de viaje
    Route::get('rides/create', 'RideController@create')->name('rides.create');
    // Procesamiento y almacenamiento
    Route::post('rides', 'RideController@store')->name('rides.store');
    // Mostrar detalles de la solicitud
    Route::get('rides/{ride}', 'RideController@show')->name('rides.show'); 
    //Obtienne los viajes de un usuario cliente
    Route::get('rides/client/index', 'RideController@clientindex')->name('rides.clientindex'); 
    //para eliminar ride desde usuario cliente
    Route::delete('/rides/{ride}', 'RideController@destroy')->name('rides.destroy');
    //actualizacion de conductores para rides y listado de rides para consulta
    Route::get('/driver/index', 'RideController@driverindex')->name('rides.driverindex');
    Route::post('/rides/{ride}/status', 'RideController@updateStatus')->name('rides.updateStatus');

    //rutas para consulta de conductores disponibles
    Route::post('/api/driver/status', [DriverStatusController::class, 'update']);
    Route::get('/api/driver/status', [DriverStatusController::class, 'show']);
    Route::get('/vehicles/available', [DriverStatusController::class, 'available']);

    Route::get('/user/profile', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/profile', [UserController::class, 'updateProfile'])->name('user.updateProfile');

});

// Registro de administradores
Route::get('admin/register', 'AdminRegisterController@showRegistrationForm')->name('admin.register.form');
Route::post('admin/register', 'AdminRegisterController@register')->name('admin.register');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('service-types', 'ServiceTypeController');
    // Mantenimiento de usuarios
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Mantenimiento de vehículos
    Route::get('/admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('/admin/vehicles/create', [VehicleController::class, 'create'])->name('admin.vehicles.create');
    Route::post('/admin/vehicles', [VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('/admin/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('admin.vehicles.edit');
    Route::put('/admin/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('/admin/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
});
