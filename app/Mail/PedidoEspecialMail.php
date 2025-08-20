<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoEspecialMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;


    public function __construct($pedido)
    {
        $this->pedido = $pedido;

    }

    public function build()
    {
        return $this->subject('🔔 Pedido de Negociación Especial Recibido')
                    ->view('correos.pedidoNegociacion');
    }
}