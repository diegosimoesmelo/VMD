<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/alunos', [StudentController::class, 'index'])->name('students.index');
    Route::get('/alunos/cadastrar', [StudentController::class, 'create'])->name('students.create');
    Route::post('/alunos/cadastrar', [StudentController::class, 'store'])->name('students.store');
    Route::get('/alunos/{student}/editar', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/alunos/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::post('/alunos/{student}/avancar-status', [StudentController::class, 'advanceStatus'])->name('students.advance-status');
    Route::get('/professores', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/professores/cadastrar', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/professores/cadastrar', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/professores/{teacher}/editar', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/professores/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::get('/veiculos', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/veiculos/cadastrar', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/veiculos/cadastrar', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/veiculos/{vehicle}/editar', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/veiculos/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::get('/agendamentos', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/agendamentos', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/agendamentos/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
