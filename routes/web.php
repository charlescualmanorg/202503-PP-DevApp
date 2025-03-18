<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', 'UserController');
    Route::resource('drivers', 'DriverController');

});

// Registro de administradores
Route::get('admin/register', 'AdminRegisterController@showRegistrationForm')->name('admin.register.form');
Route::post('admin/register', 'AdminRegisterController@register')->name('admin.register');


Route::middleware(['auth'])->group(function () {
    // Página de selección para nuevo viaje
    Route::get('rides/new', 'RideController@new')->name('rides.new');
    // Formulario para crear solicitud de viaje
    Route::get('rides/create', 'RideController@create')->name('rides.create');
    // Procesamiento y almacenamiento
    Route::post('rides', 'RideController@store')->name('rides.store');
    // Mostrar detalles de la solicitud
    Route::get('rides/{ride}', 'RideController@show')->name('rides.show');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function () {
    Route::resource('service-types', 'ServiceTypeController');
});

use App\Http\Controllers\DriverStatusController;

Route::post('/api/driver/status', [DriverStatusController::class, 'update']);
Route::get('/api/driver/status', [DriverStatusController::class, 'show']);
Route::get('/vehicles/available', [DriverStatusController::class, 'available']);