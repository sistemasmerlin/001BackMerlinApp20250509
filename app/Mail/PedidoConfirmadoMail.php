<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $encabezados;
    public $detalles;
    public $subtotal_pedido;
    public $subtotal_descuento;
    public $cotizacion;
    public $numero_carro;

    public function __construct($encabezados, $detalles, $subtotal_pedido, $subtotal_descuento, $cotizacion = null,$numero_carro = null)
    {
        $this->encabezados = $encabezados;
        $this->detalles = $detalles;
        $this->subtotal_pedido = $subtotal_pedido;
        $this->subtotal_descuento = $subtotal_descuento;
        $this->cotizacion = $cotizacion;
        $this->numero_carro = $numero_carro;       
    }

    public function build()
    {
        return $this->subject('Pedido Confirmado')
                    ->view('correos.pedido-confirmado');
    }

}
