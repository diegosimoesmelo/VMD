<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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
    Route::get('/professores', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/professores/cadastrar', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/professores/cadastrar', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/professores/{teacher}/editar', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/professores/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::view('/agendamentos', 'appointments.index')->name('appointments.index');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
