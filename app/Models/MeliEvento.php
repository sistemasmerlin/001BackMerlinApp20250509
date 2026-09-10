<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeliEvento extends Model
{
    use HasFactory;

    protected $table = 'meli_eventos';

    protected $fillable = [
        'tipo',
        'metodo',
        'url',
        'topic',
        'resource',
        'user_id',
        'application_id',
        'query_params',
        'payload',
        'raw_body',
        'headers',
        'ip',
        'estado',
        'error',
    ];

    protected $casts = [
        'query_params' => 'array',
        'payload' => 'array',
        'headers' => 'array',
    ];
}