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

Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin', 'AdminController@index');
});

Route::middleware(['role:conductor'])->group(function () {
    Route::get('/conductor', 'DriverController@index');
});

Route::middleware(['role:cliente'])->group(function () {
    Route::get('/cliente', 'ClientController@index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', 'UserController');
    Route::resource('drivers', 'DriverController');

});
        // Página de selección para nuevo viaje
        Route::get('rides/new', 'RideController@new')->name('rides.new');
        // Formulario para crear solicitud de viaje
        Route::get('rides/create', 'RideController@create')->name('rides.create');
        // Procesamiento y almacenamiento
        Route::post('rides', 'RideController@store')->name('rides.store');
        // Mostrar detalles de la solicitud
        Route::get('rides/{ride}', 'RideController@show')->name('rides.show');