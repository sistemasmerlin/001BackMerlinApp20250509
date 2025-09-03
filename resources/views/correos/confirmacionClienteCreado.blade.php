@component('mail::message')
# Confirmación de cliente creado para la cotización {{$datos['idCotizacion']}}

**NIT:** {{ $datos['nuevoNit'] ?? '' }}

**Sucursal:** {{ $datos['nuevaSucursal'] ?? '' }}  
**Lista de precios:** {{ $datos['nuevaListaPrecios'] ?? '' }}  
**Punto de envío:** {{ $datos['nuevoPuntoEnvio'] ?? '' }}

@component('mail::panel')
Si hay inconsistencias, por favor responder este correo con los ajustes necesarios.
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
