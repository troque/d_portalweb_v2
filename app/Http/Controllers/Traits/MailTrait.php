<?php

namespace App\Http\Controllers\Traits;

use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

trait MailTrait
{

    /**
     *
     */
    public static function sendMail($correos, $nombre_usuario, $asunto, $contenido, $archivos = [null], $correoscc = [null], $correosbbc = [null], $arrayEnviar = [], $recuperarContrasena = 0, $contrasena = "")
    {
        $correos = $correos;
        $urlValidarToken = !empty($arrayEnviar) ? $arrayEnviar["rutaVerificarCorreo"] : "";

        $datos_mail = new SendMail($asunto, $nombre_usuario, $contenido, $archivos, $urlValidarToken, $recuperarContrasena, $contrasena);
        Mail::to($correos)
            ->cc($correoscc)
            ->bcc($correosbbc)
            ->send($datos_mail);
    }
}