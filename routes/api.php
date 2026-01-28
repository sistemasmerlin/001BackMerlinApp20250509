<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TercerosController;
use App\Http\Controllers\Api\ProductosController;
use App\Http\Controllers\Api\PromocionesController;
use App\Http\Controllers\Api\FleteController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\FacturasController;
use App\Http\Controllers\Api\CarteraController;
use App\Http\Controllers\Api\NoticiasController;
use App\Http\Controllers\Api\BackorderController;
use App\Http\Controllers\Api\MotivosVisitaController;
use App\Http\Controllers\Api\ComercialController;
use App\Http\Controllers\Api\IntegracionTiendaController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/perfil', function (\Illuminate\Http\Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/terceros/{id}', [TercerosController::class, 'index']);

Route::middleware('auth:sanctum')->get('/terceros/consulta/{codigo_asesor}/{nit}', [TercerosController::class, 'consultarTercero']);

Route::middleware('auth:sanctum')->get('/productos', [ProductosController::class, 'index']);

Route::middleware('auth:sanctum')->get('/productos/tienda', [IntegracionTiendaController::class, 'index']);

Route::middleware('auth:sanctum')->get('/promociones', [PromocionesController::class, 'index']);

Route::middleware('auth:sanctum')->get('/fletes', [FleteController::class, 'index']);

Route::middleware('auth:sanctum')->post('/pedidos/guardar', [PedidoController::class, 'guardar']);

Route::middleware('auth:sanctum')->post('/pedidos/guardar/especial', [PedidoController::class, 'guardarPedidoEspecial']);

Route::middleware('auth:sanctum')->get('/backorders/{codigo_asesor}', [BackorderController::class, 'index']);

Route::middleware('auth:sanctum')->post('/backorders/crear/pedido', [BackorderController::class, 'crearPedido']);

Route::middleware('auth:sanctum')->get('/pedidos/error/{codigo_asesor}', [PedidoController::class, 'pedidosConError']);

Route::middleware('auth:sanctum')->get('/pedidos/erp/{codigo_asesor}', [PedidoController::class, 'pedidosErp']);

Route::middleware('auth:sanctum')->get('/pedidos/especiales/{codigo_asesor}', [PedidoController::class, 'pedidosEspeciales']);

Route::middleware('auth:sanctum')->get('/pedidos/especial/detalle/{id_pedido}', [PedidoController::class, 'detallePedidoEspecial']);

Route::middleware('auth:sanctum')->get('/pedidos/erp/detalle/{prefijo}/{consecutivo}', [PedidoController::class, 'detallePedidoErp']);

Route::middleware('auth:sanctum')->get('/facturas/erp/asesor/{codigo_asesor}', [FacturasController::class, 'cargarFacturasAsesor']);

Route::middleware('auth:sanctum')->get('/facturas/erp/cliente/{nit}', [FacturasController::class, 'cargarFacturasCliente']);

Route::middleware('auth:sanctum')->get('/facturas/consultar/{prefijo}/{consecutivo}', [FacturasController::class, 'consultarFactura']);

Route::middleware('auth:sanctum')->get('/facturas/consultar/erp/fecha/cliente/{codico_asesor}/{nit}/{desde}/{hasta}', [FacturasController::class, 'cargarFacturasFechaCliente']);

Route::middleware('auth:sanctum')->get('/cartera/cliente/{nit}', [CarteraController::class, 'cargarFacturasCliente']);

Route::middleware('auth:sanctum')->get('/noticias', [NoticiasController::class, 'index']);

Route::middleware('auth:sanctum')->get('/motivos/visita', [MotivosVisitaController::class, 'index']);

Route::middleware('auth:sanctum')->post('/motivos/visita/guardar', [MotivosVisitaController::class, 'store']);

//Indicadores

Route::middleware('auth:sanctum')->get(
    '/indicadores/clientes/impactados/{asesor}/{periodo}',
    [ComercialController::class, 'clientesImpactados']
);

Route::middleware('auth:sanctum')->get(
    '/indicadores/clientes/facturacion/{asesor}/{periodo}',
    [ComercialController::class, 'efectividadVentas']
);

Route::middleware('auth:sanctum')->get(
    '/indicadores/clientes/cartera/{asesor}/{periodo}/{categoria_asesor}',
    [CarteraController::class, 'recuadoPresupuesto']
);



