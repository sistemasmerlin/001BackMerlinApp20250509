<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Permisos\Index as PermisosIndex;
use App\Livewire\Admin\Terceros\Index as TercerosIndex;
use App\Livewire\Admin\Promociones\Index as PromocionesIndex;
use App\Livewire\Admin\Fletes\Index as FletesIndex;
use App\Livewire\Admin\InteresesCartera\Index as InteresesCarteraIndex;
use App\Livewire\Admin\Pedidos\Index as PedidosIndex;
use App\Livewire\Admin\BackOrder\Index as BackOrderIndex;
use App\Livewire\Admin\Noticias\Index as NoticiasIndex;
use App\Livewire\Admin\Pedidos\Detalle as PedidosDetalle;
use App\Livewire\Admin\MotivosVisitas\Index as MotivosVisitasIndex;
use App\Livewire\Admin\ReporteVisitas\Index as ReporteVisitasIndex;
use App\Livewire\Admin\Promociones\Detalle;
use App\Livewire\Admin\RelacionAsesores\Index as RelacionAsesoresIndex;
use App\Livewire\Admin\PresupuestosComerciales\Index as PresupuestosComercialesIndex;

use App\Http\Controllers\Admin\TercerosController;
use App\Http\Controllers\Admin\PromocionesController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\PresupuestoComercialController;
use App\Http\Controllers\Api\InteresesCarteraController;

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
    Route::get('/usuarios', UsuariosIndex::class)->middleware('can:Usuarios')->name('usuarios.index');
    Route::get('/roles', RolesIndex::class)->name('roles.index');
    Route::get('/permisos', PermisosIndex::class)->name('permisos.index');
    Route::get('/presupuesto', PresupuestosComercialesIndex::class)->name('presupuesto.index');

    Route::get('/terceros', TercerosIndex::class)->name('terceros.index');
    Route::get('/terceros/consultar', [TercerosController::class, 'index']);
    Route::get('/terceros/todos', [TercerosController::class, 'index']);

    Route::get('/promociones', PromocionesIndex::class)->name('promociones.index');

    Route::get('/promociones/{promocion}/detalle', Detalle::class)->name('admin.promociones.detalle');

    Route::get('/pedidos', PedidosIndex::class)->name('pedidos.index');

    Route::get('/motivosVisita', MotivosVisitasIndex::class)->name('motivosVisita.index');

    Route::get('/reporte/visitas', ReporteVisitasIndex::class)->name('reporte/visitas.index');

    Route::get('/backOrder', BackOrderIndex::class)->name('backOrder.index');

    Route::get('/noticias', NoticiasIndex::class)->name('noticias.index');

    Route::get('/pedidos/{pedido}/detalle', PedidosDetalle::class)->name('admin.pedidos.detalle');

    Route::get('/intereses/cartera', InteresesCarteraIndex::class)->name('intereses.cartera.index');

    Route::get('/fletes', FletesIndex::class)->name('fletes.index');

    Route::get('/relacion/asesores', RelacionAsesoresIndex::class)->name('relacion.asesores.index');

    Route::get('/enviar-envio/{id}', [PedidoController::class, 'enviarPedido'])->name('pedidos.enviar');

    Route::get('/cartera/intereses/calcular', [InteresesCarteraController::class, 'calcularInteresesDiarios'])->name('cartera.intereses.calcular');

    Route::get('/presupuestos-comerciales/plantilla',[PresupuestoComercialController::class, 'plantilla'])->name('presupuestos.plantilla');
});


require __DIR__ . '/auth.php';
