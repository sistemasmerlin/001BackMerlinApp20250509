<?php

namespace App\Mail;

use App\Models\Pqrs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PqrsCreadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pqrs;

    public function __construct(Pqrs $pqrs)
    {
        $this->pqrs = $pqrs;
    }

    public function build()
    {
        return $this->subject('Nueva PQRS #'.$this->pqrs->id)
            ->view('correos.pqrs-creada');
    }
}