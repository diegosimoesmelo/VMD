<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonMonitoringController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentLessonReportController;
use App\Http\Controllers\StudentProfileReportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
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
    Route::get('/primeiro-acesso/senha', [PasswordChangeController::class, 'edit'])->name('password.change.edit');
    Route::put('/primeiro-acesso/senha', [PasswordChangeController::class, 'update'])->name('password.change.update');

    Route::middleware('password.change.required')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('role:gerente,administrativo')->group(function () {
            Route::get('/alunos', [StudentController::class, 'index'])->name('students.index');
            Route::get('/alunos/cadastrar', [StudentController::class, 'create'])->name('students.create');
            Route::post('/alunos/cadastrar', [StudentController::class, 'store'])->name('students.store');
            Route::get('/alunos/{student}/editar', [StudentController::class, 'edit'])->name('students.edit');
            Route::put('/alunos/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::post('/alunos/{student}/avancar-status', [StudentController::class, 'advanceStatus'])->name('students.advance-status');
            Route::post('/alunos/{student}/compras-aulas', [StudentController::class, 'storeLessonPurchase'])->name('students.lesson-purchases.store');
            Route::get('/alunos/{student}/recibo-cadastro', [ReceiptController::class, 'registration'])->name('students.receipts.registration.show');
            Route::get('/alunos/{student}/recibo-cadastro/pdf', [ReceiptController::class, 'registrationPdf'])->name('students.receipts.registration.download');
            Route::get('/alunos/{student}/aulas/pdf', [StudentLessonReportController::class, 'download'])->name('students.lessons.pdf');
            Route::get('/alunos/{student}/ficha/pdf', [StudentProfileReportController::class, 'download'])->name('students.profile.pdf');
            Route::get('/compras-aulas/{purchase}/recibo', [ReceiptController::class, 'purchase'])->name('lesson-purchases.receipts.show');
            Route::get('/compras-aulas/{purchase}/recibo/pdf', [ReceiptController::class, 'purchasePdf'])->name('lesson-purchases.receipts.download');
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
            Route::get('/controle-de-aulas', [LessonMonitoringController::class, 'index'])->name('lesson-monitoring.index');
            Route::post('/controle-de-aulas/{appointment}', [LessonMonitoringController::class, 'update'])->name('lesson-monitoring.update');

            Route::middleware('role:gerente')->group(function () {
                Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
                Route::get('/usuarios/cadastrar', [UserController::class, 'create'])->name('users.create');
                Route::post('/usuarios/cadastrar', [UserController::class, 'store'])->name('users.store');
                Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
                Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
                Route::post('/usuarios/{user}/resetar-senha', [UserController::class, 'resetPassword'])->name('users.reset-password');
            });
        });
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
