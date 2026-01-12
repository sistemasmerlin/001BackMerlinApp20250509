<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cedula', 
        'codigo_asesor', 
        'codigo_recibos',
        'categoria_asesor'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Usuarios que este usuario (asesor) ha relacionado
    public function relacionados()
    {
        return $this->belongsToMany(User::class, 'relacion_asesores', 'asesor_id', 'relacionado_id');
    }

    // Usuarios que han relacionado a este usuario
    public function relacionadoPor()
    {
        return $this->belongsToMany(User::class, 'relacion_asesores', 'relacionado_id', 'asesor_id');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function presupuestosComerciales()
    {
            return $this->hasMany(PresupuestoComercial::class, 'codigo_asesor', 'codigo_asesor');
    }
}
