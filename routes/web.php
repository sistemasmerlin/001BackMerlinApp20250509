<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Permisos\Index as PermisosIndex;
use App\Livewire\Admin\Terceros\Index as TercerosIndex;

use App\Http\Controllers\Admin\TercerosController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/usuarios', UsuariosIndex::class)->name('usuarios.index');
    Route::get('/roles', RolesIndex::class)->name('roles.index');
    Route::get('/permisos', PermisosIndex::class)->name('permisos.index');

    Route::get('/terceros', TercerosIndex::class)->name('terceros.index');


    Route::get('/terceros/consultar', [TercerosController::class, 'index']);

    Route::get('/terceros/todos', [TercerosController::class, 'index']);
});


require __DIR__.'/auth.php';
