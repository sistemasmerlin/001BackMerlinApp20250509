<?php


namespace App\Xml;
use SoapClient;
use App\Models\Pedido as PedidoModel;
use Illuminate\Support\Facades\DB;

class PedidoBackOrder {

    public function generarXml($backorder)
    {
                $mensaje = "ERROR AL ENVIAR EL XML";
        $status = "error";
        $xmlResult = null;
        $importar = "";

        $nombreConexion = env('SIESA_NOMBRE_CONEXION');
        $usuario = env('SIESA_USUARIO');
        $clave = env('SIESA_CLAVE');
        $url = env('SIESA_WSDL_URL');

        try {

            $opts = array('ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false));
            $params = array ('encoding' => 'UTF-8', 'verifypeer' => false, 'verifyhost' => false, 'soap_version' => SOAP_1_2,'trace' => 1, 'exceptions' => 1, "connection_timeout" => 180, 'stream_context' => stream_context_create($opts));
            //$url = 'http://192.168.140.249/WSUNOEE/WSUNOEE.asmx?WSDL';
            $url = 'http://192.168.140.236/WSUNOEE/WSUNOEE.asmx?WSDL';
            $client = new \SoapClient($url, $params);

            $prefijo = $pedido->prefijo ?? 'PAM';
            $lista_precio = $pedido->lista_precio ?? '001';
            $nota = str_pad(substr($pedido->observaciones ?? 'Desde backorder', 0, 2000), 2000);
            $fechaActual = now()->format('Ymd');
            $fechaEntrega = now()->addDay()->format('Ymd');
            //$nit = str_pad(substr($pedido->nit, 0, 15), 15, ' ', STR_PAD_RIGHT);

            $idCliente = $backorder->pedido->nit;
            $flete = 0;
            $caracteresFaltantesCliente = 15 - strlen($idCliente);
            $nit =  $idCliente.str_repeat(' ', $caracteresFaltantesCliente);
            $complemento_oc = $backorder->pedido->nota.'-'.$this->generarCodigoAleatorio();
            $id_sucursal_simple = $backorder->pedido->id_sucursal;
            $id_punto_envio = $backorder->pedido->direccionEnvio['id_punto_envio'];
            $id_sucursal = str_pad(substr($backorder->pedido->id_sucursal, 0, 7), 7);
            $orden_compra = str_pad(substr($complemento_oc, 0, 35), 35);
            //$id_estado_pedido = $pedido->id_estado_pedido ?? '001';
            $condicion_pago = $pedido->condicion_pago ?? 'CON';
            $id_estado_pedido = 2;

            $ulimoDatoFila2 = str_repeat(' ', 15) . $id_punto_envio . str_repeat(' ', 333) . '200000000';
            $linea2 = '<Linea>'.'000000204300003003111003'.$prefijo.'00000001'.$fechaActual.'502'.$id_estado_pedido.'0'.$nit.$id_sucursal_simple.$nit.$id_sucursal.'003'.$fechaEntrega.'001'.$orden_compra.'COPCOP00000001.0000COP00000001.0000'.$condicion_pago.'0'.$nota.$ulimoDatoFila2.'</Linea>';

            $numero_linea = 3;
            $consecutivo = 1;
            $filasProductos = '';

            $totalProductos = (is_array($backorder->detalles) ? count($backorder->detalles) : $backorder->detalles->count()) - 1;

            foreach ($backorder->detalles as $key => $value) {

                if( $value['cantidad_enviar'] > 0){
                    $numeroFila = str_pad($numero_linea, 7, '0', STR_PAD_LEFT);
                    $consecutivo2 = str_pad($consecutivo, 10, '0', STR_PAD_LEFT);
                    $referencia = str_pad($value['referencia'], 110, ' ', STR_PAD_RIGHT);
                    $campoMotivo = str_pad('01', 50, ' ', STR_PAD_RIGHT); // 2 caracteres de código + 48 espacios
                    $listaPrecio = str_pad($lista_precio, 3, ' ', STR_PAD_RIGHT);
                    $unidadMedida = str_pad('UNID', 4, ' ', STR_PAD_RIGHT);
                    $cantidadPedida = str_pad((string) $value['cantidad_enviar'], 15, '0', STR_PAD_LEFT) . '.0000';
                    $precio = str_pad('001', 15, '0', STR_PAD_LEFT) . '.0000';

                    $comentario = str_pad($orden_compra . ' Pedido tienda online', 2255, ' ', STR_PAD_RIGHT);

                    $filasProductos .= '<Linea>' .
                        $numeroFila . '04310004003003' . $prefijo . '00000001' . $consecutivo2 . '0000000' .
                        $referencia . '00337501010003' . $campoMotivo . $fechaActual . '001   ' .
                        $unidadMedida . $cantidadPedida . '000000000000000.0000' . $precio . '0' . $comentario . '50   </Linea>';

                    $numero_linea++;
                    $consecutivo++;
                
                    if ($value['descuento'] > 0) {
                        // Formatear número de línea con 7 dígitos, ceros a la izquierda
                        $numeroFila = str_pad($numero_linea, 7, '0', STR_PAD_LEFT);

                        // Formatear descuento como entero de 3 dígitos + '.0000'
                        $descuentoEntero = intval($value['descuento']);
                        $porcentajeDescuento = str_pad($descuentoEntero, 3, '0', STR_PAD_LEFT) . '.0000';

                        // Construir línea XML
                        $filasProductos .= '<Linea>' . $numeroFila . '04320001003003' . $prefijo . '00000001' . $consecutivo2 . '011' . $porcentajeDescuento . '000000000000000.0000</Linea>';

                        $numero_linea++;
                    }
                    }
                }


                if ($flete > 0) {
                    // Función auxiliar para completar con ceros o espacios
                    $padLeft = fn($value, $length, $char = '0') => str_pad($value, $length, $char, STR_PAD_LEFT);
                    $padRight = fn($value, $length, $char = ' ') => str_pad($value, $length, $char, STR_PAD_RIGHT);

                    $numeroFila     = $padLeft($numero_linea, 7);
                    $consecutivo2   = $padLeft($consecutivo, 10);
                    $referenciaFlete = $padRight('ZLE99999', 110);
                    $precioFlete     = $padLeft($flete, 15) . '.0000';
                    $campoMotivo     = '01' . str_repeat(' ', 48);
                    $comentario      = $padRight('Pedido tienda online', 2255);

                    $filasProductos .= '<Linea>' .
                        $numeroFila . 
                        '04310004003003' . 
                        $prefijo . 
                        '00000001' . 
                        $consecutivo2 . 
                        '0000000' . 
                        $referenciaFlete . 
                        '00337501010003' . 
                        $campoMotivo . 
                        $fechaActual . 
                        '001003UNID000000000000001.0000000000000000000.0000' . 
                        $precioFlete . 
                        '0' . 
                        $comentario . 
                        '52   </Linea>';

                    $numero_linea++;
                    $consecutivo++;
                }


                $filaFinal = str_pad($numero_linea, 7, '0', STR_PAD_LEFT);
                $filasProductos .= '<Linea>' . $filaFinal . '99990001003</Linea>';
                $importar = '<Importar><NombreConexion>'.$nombreConexion.'</NombreConexion>
                <IdCia>3</IdCia><Usuario>'.$usuario.'</Usuario><Clave>'.$clave.'</Clave><Datos><Linea>000000100000001003</Linea>'.$linea2.$filasProductos.'</Datos></Importar>';


                    
        // return $importar;

            $importacionXML = $client->ImportarXML(['pvstrDatos'=>$importar,'printTipoError'=>0]);

            $xmlResult = $importacionXML;
            if($xmlResult->printTipoError == 0){
                $mensaje = "Xml enviado con exito!";
                $status = "success";
            }

    	}catch(SoapFault $fault) {
	        $mensaje = "Error";
            $status = "error";
	    }

        return $array = [
            'status' => $status,
            'xmlResult'=>$xmlResult,
            'mensaje'=>$mensaje,
        ];
    }

    public function generarCodigoAleatorio() {
        $letras = '';
        for ($i = 0; $i < 2; $i++) {
            $letras .= chr(rand(65, 90)); // Letras de A (65) a Z (90)
        }

        $numeros = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // Números del 00 al 99

        return $letras . $numeros;
    }

}
