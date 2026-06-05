<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecibosEncabezado extends Model
{
    use SoftDeletes;

    protected $table = 'recibos_encabezados';

    protected $guarded = [];

    public function caja()
    {
        return $this->hasOne(ReciboCaja::class, 'recibo_encabezado_id');
    }

    public function cxcs()
    {
        return $this->hasMany(ReciboCajaCxc::class, 'recibo_encabezado_id');
    }

    public function ingresos()
    {
        return $this->hasMany(ReciboCajaIngreso::class, 'recibo_encabezado_id');
    }

    public function retenciones()
    {
        return $this->hasMany(ReciboCajaRetencion::class, 'recibo_encabezado_id');
    }
}