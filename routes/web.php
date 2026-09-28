<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AreaNoodleController;
use App\Http\Controllers\Admin\NoodleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\FirstPasswordController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/buat-password', [FirstPasswordController::class, 'edit'])->name('password.first.edit');
    Route::put('/buat-password', [FirstPasswordController::class, 'update'])->name('password.first.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('password.changed')->group(function () {
        Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

        Route::get('/', function () {
            if (request()->user()->isSuperadmin()) {
                return redirect()->route('admin.users.index');
            }

            return view('pages.dashboard.ecommerce', ['title' => 'Dashboard']);
        })->name('dashboard');
        Route::get('/calendar', fn () => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
        Route::get('/profile', fn () => view('pages.profile', ['title' => 'Profile']))->name('profile');
        Route::get('/form-elements', fn () => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
        Route::get('/basic-tables', fn () => view('pages.tables.basic-tables', ['title' => 'Basic Tables']))->name('basic-tables');
        Route::get('/blank', fn () => view('pages.blank', ['title' => 'Blank']))->name('blank');
        Route::get('/error-404', fn () => view('pages.errors.error-404', ['title' => 'Error 404']))->name('error-404');
        Route::get('/line-chart', fn () => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
        Route::get('/bar-chart', fn () => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');
        Route::get('/alerts', fn () => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
        Route::get('/avatars', fn () => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
        Route::get('/badge', fn () => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badges');
        Route::get('/buttons', fn () => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
        Route::get('/image', fn () => view('pages.ui-elements.images', ['title' => 'Images']))->name('images');
        Route::get('/videos', fn () => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');

        Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::get('/maintenance/noodle/noodle', [NoodleController::class, 'index'])->name('maintenance.noodle.index');
            Route::get('/maintenance/noodle/data', [NoodleController::class, 'data'])->name('maintenance.noodle.data');
            Route::get('/maintenance/noodle/noodle/create', [NoodleController::class, 'create'])->name('maintenance.noodle.create');
            Route::get('/maintenance/area-noodle', [AreaNoodleController::class, 'index'])->name('maintenance.area-noodle.index');
            Route::get('/maintenance/area-noodle/data', [AreaNoodleController::class, 'data'])->name('maintenance.area-noodle.data');
            Route::get('/maintenance/area-noodle/create', [AreaNoodleController::class, 'create'])->name('maintenance.area-noodle.create');
        });
    });
});

Route::redirect('/signin', '/login');
