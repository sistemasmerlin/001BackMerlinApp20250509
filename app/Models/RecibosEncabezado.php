<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecibosEncabezado extends Model
{
    use SoftDeletes;

    protected $table = 'recibos_encabezados';

    protected $fillable = [
        'codigo_docto',
        'fecha_recibo',

        'id_vendedor',
        'nombre_asesor',
        'email_asesor',

        'codigo_recibo',

        'razon_social',
        'nit_cliente',
        'email_cliente',

        'numero_soporte',
        'fecha_comprobante',

        'total_recibido',
        'total_restante',

        'id_banco',


        'notas',
        'notas_rechazo',
        'notas_pendiente',

        'retencion',
        'reteIva',

        'estado',

        'id_recibo_efectivo',
        'valor_recibo_efectivo',

        'usuarioAsignado',

        'fecha_revision',
        'fecha_aprobacion',
        'fecha_exportacion',
        'fecha_cliente_creado',

        'adjunto_nombre_archivo',
        'ubicacion',
    ];

    protected $casts = [
        'fecha_recibo' => 'datetime',
        'fecha_comprobante' => 'date',

        'total_recibido' => 'decimal:2',
        'total_restante' => 'decimal:2',

        'retencion' => 'decimal:2',
        'reteIva' => 'decimal:2',

        'valor_recibo_efectivo' => 'decimal:2',

        'fecha_revision' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_exportacion' => 'datetime',
        'fecha_cliente_creado' => 'datetime',
    ];

    public function caja()
    {
        return $this->hasOne(
            ReciboCaja::class,
            'recibo_encabezado_id'
        );
    }

    public function cxcs()
    {
        return $this->hasMany(
            ReciboCajaCxc::class,
            'recibo_encabezado_id'
        );
    }

    public function ingresos()
    {
        return $this->hasMany(
            ReciboCajaIngreso::class,
            'recibo_encabezado_id'
        );
    }

    public function retenciones()
    {
        return $this->hasMany(
            ReciboCajaRetencion::class,
            'recibo_encabezado_id'
        );
    }
}