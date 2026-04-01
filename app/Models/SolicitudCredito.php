<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudCredito extends Model
{
    use SoftDeletes;

    protected $table = 'solicitudes_credito';

    protected $fillable = [
        'cod_depto',
        'depto',
        'cod_ciudad',
        'ciudad',
        'fecha_solicitud',
        'razon_social',
        'nombre_comercial',
        'nit_cc',
        'representante_legal',
        'identificacion_representante',
        'direccion_negocio',
        'barrio',
        'telefono_fijo',
        'celular',
        'correo_electronico',
        'contacto_compras',
        'telefono_compras',
        'correo_compras',
        'contacto_tesoreria',
        'telefono_tesoreria',
        'correo_tesoreria',
        'contacto_factura_electronica',
        'telefono_factura_electronica',
        'correo_factura_electronica',
        'rte_fuente',
        'rte_iva',
        'rte_ica',
        'antiguedad_comercial',
        'tiempo_antiguedad',
        'tipo_negocio',
        'puntos_venta',
        'canal_tradicional',
        'canal_corporativo',
        'numero_empleados',
        'ventas_proyectadas_mes',
        'cupo_sugerido',
        'autorizacion_cod_depto',
        'autorizacion_depto',
        'autorizacion_cod_ciudad',
        'autorizacion_ciudad',
        'autorizacion_fecha',
        'autorizacion_nombre_1',
        'autorizacion_documento_1',
        'autorizacion_lugar_expedicion_1',
        'autorizacion_razon_social',
        'autorizacion_nit_cc',
        'autorizacion_nombre_2',
        'autorizacion_documento_2',
        'autorizacion_lugar_expedicion_2',
        'autorizacion_telefono_fijo',
        'autorizacion_celular',
        'autorizacion_correo',
        'autorizacion_direccion',
        'pdf_unificado_disk',
        'pdf_unificado_path',
        'pdf_unificado_nombre',
        'auco_code',
        'auco_package',
        'auco_status',
        'auco_response',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'autorizacion_fecha' => 'date',
        'rte_fuente' => 'boolean',
        'rte_iva' => 'boolean',
        'rte_ica' => 'boolean',
        'ventas_proyectadas_mes' => 'decimal:2',
        'cupo_sugerido' => 'decimal:2',
        'auco_response' => 'array',
    ];

    public function referencias()
    {
        return $this->hasMany(SolicitudCreditoReferencia::class, 'solicitud_credito_id');
    }

    public function direcciones()
    {
        return $this->hasMany(SolicitudCreditoDireccion::class, 'solicitud_credito_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}