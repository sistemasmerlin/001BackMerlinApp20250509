<?php

return [

    'nombre_conexion' => env('SIESA_NOMBRE_CONEXION', 'default'),
    'usuario'         => env('SIESA_USUARIO', 'usuario'),
    'clave'           => env('SIESA_CLAVE', 'clave'),
    'wsdl_url'        => env('SIESA_WSDL_URL', 'http://192.168.140.236/WSUNOEE/WSUNOEE.asmx?WSDL'),

    'nombre_conexion_pruebas' => env('SIESA_NOMBRE_CONEXION_PRUEBAS', 'default'),
    'usuario_pruebas'         => env('SIESA_USUARIO_PRUEBAS', 'usuario'),
    'clave_pruebas'           => env('SIESA_CLAVE_PRUEBAS', 'clave'),
    'wsdl_url_pruebas'        => env('SIESA_WSDL_URL_PRUEBAS', 'http://192.168.140.236/WSUNOEE/WSUNOEE.asmx?WSDL'),
];
