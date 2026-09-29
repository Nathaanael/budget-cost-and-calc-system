<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AreaNoodleController;
use App\Http\Controllers\Admin\FinishedGoodController;
use App\Http\Controllers\Admin\FactoryController;
use App\Http\Controllers\Admin\Formula\FormulaFgController;
use App\Http\Controllers\Admin\Formula\FormulaNdlController;
use App\Http\Controllers\Admin\NoodleController;
use App\Http\Controllers\Admin\RawMaterialController;
use App\Http\Controllers\Admin\ReferenceController;
use App\Http\Controllers\Admin\SynonimController;
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
            Route::get('/maintenance/finished-good', [FinishedGoodController::class, 'index'])->name('maintenance.finished-good.index');
            Route::get('/maintenance/finished-good/data', [FinishedGoodController::class, 'data'])->name('maintenance.finished-good.data');
            Route::get('/maintenance/finished-good/create', [FinishedGoodController::class, 'create'])->name('maintenance.finished-good.create');
            Route::get('/maintenance/raw-material', [RawMaterialController::class, 'index'])->name('maintenance.raw-material.index');
            Route::get('/maintenance/raw-material/data', [RawMaterialController::class, 'data'])->name('maintenance.raw-material.data');
            Route::get('/maintenance/raw-material/create', [RawMaterialController::class, 'create'])->name('maintenance.raw-material.create');
            Route::get('/maintenance/formula/ndl', [FormulaNdlController::class, 'index'])->name('maintenance.formula.ndl');
            Route::get('/maintenance/formula/fg', [FormulaFgController::class, 'index'])->name('maintenance.formula.fg');
            Route::get('/maintenance/reference', [ReferenceController::class, 'index'])->name('maintenance.reference.index');
            Route::get('/maintenance/reference/data', [ReferenceController::class, 'data'])->name('maintenance.reference.data');
            Route::get('/maintenance/reference/create', [ReferenceController::class, 'create'])->name('maintenance.reference.create');
            Route::get('/maintenance/factory', [FactoryController::class, 'index'])->name('maintenance.factory.index');
            Route::get('/maintenance/factory/data', [FactoryController::class, 'data'])->name('maintenance.factory.data');
            Route::get('/maintenance/factory/create', [FactoryController::class, 'create'])->name('maintenance.factory.create');
            Route::get('/maintenance/synonim', [SynonimController::class, 'index'])->name('maintenance.synonim.index');
            Route::get('/maintenance/synonim/data', [SynonimController::class, 'data'])->name('maintenance.synonim.data');
            Route::get('/maintenance/synonim/create', [SynonimController::class, 'create'])->name('maintenance.synonim.create');
        });
    });
});

Route::redirect('/signin', '/login');
