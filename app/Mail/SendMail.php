<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    protected $usuario;
    protected $asunto;
    protected $adjuntos;
    protected $href;
    protected $recuperarContrasena;
    protected $contrasena;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $usuario, $asunto, $adjuntos, $href, $recuperarContrasena, $contrasena)
    {
        $this->usuario = $usuario;
        $this->asunto = $asunto;
        $this->adjuntos = $adjuntos;
        $this->href = $href;
        $this->recuperarContrasena = $recuperarContrasena;
        $this->contrasena = $contrasena;
        $this->subject($subject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->view(
            'emails.users.confirmation',
            [
                'usuario' => $this->usuario,
                'asunto' => $this->asunto,
                'href' => $this->href,
                'recuperarContrasena' => $this->recuperarContrasena,
                'contrasena' => $this->contrasena,
            ]
        );

        if ($this->adjuntos) {
            foreach ($this->adjuntos as $adjunto) {
                if ($adjunto != null) {
                    $mail->attach($adjunto);
                }
            }
        }

        return $mail;
    }
}