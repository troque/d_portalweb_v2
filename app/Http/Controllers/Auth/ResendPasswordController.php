<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Http\Request;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\MailTrait;

class ResendPasswordController extends Controller
{
    use MailTrait;
    use LogTrait;

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Se retorna la vista
        return view('auth.resend-password');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Se valida el formulario
        $request->validate([
            'tipo_documento' => ['required', 'string'],
            'numero_documento' => ['required', 'string', 'max:10'],
        ]);

        // Se captura la informacion
        $tipoDocumento = $request["tipo_documento"];
        $numeroDocumento = $request["numero_documento"];

        // Se consulta la informacion del usuario
        $userController = new RegisteredUserController();
        $informacionUsuario = $userController->consultarInformacionUsuario($numeroDocumento, $tipoDocumento);
        $existeUsuario = $informacionUsuario["existeUsuario"];
        $portalUserId = isset($informacionUsuario["datos"]->id) ? $informacionUsuario["datos"]->id : null;
        $estadoUsuario = isset($informacionUsuario["datos"]->estado) ? $informacionUsuario["datos"]->estado : null;
        $emailUsuario = isset($informacionUsuario["datos"]->email) ? $informacionUsuario["datos"]->email : null;

        // Se valida que exista un usuario
        if (isset($existeUsuario) && !$existeUsuario) {

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'error' => '400',
                    'msjRespuesta' => "No se encuentra información de este usuario en la base de datos.",
                ]
            );
        }

        // Se valida que el usuario se encuentre inactivo
        if (isset($estadoUsuario) && $estadoUsuario == 0) {

            // Se captura la informacion
            $correos = [$emailUsuario];
            $asunto = "PORTAL WEB - ACTIVACIÓN DE CUENTA";
            $contenido = "PORTAL WEB - ACTIVACIÓN DE CUENTA";
            $archivos = null;
            $correoscc = null;
            $correosbbc = null;
            $token = Str::random(60);
            $rutaVerificarCorreo = env("APP_URL") . '/reset-password/' . $token;
            $arrayEnviar = [
                "rutaVerificarCorreo" => $rutaVerificarCorreo
            ];

            // Se procede a enviar el correo electronico
            MailTrait::sendMail(
                $correos,
                "",
                $asunto,
                $contenido,
                $archivos,
                $correoscc,
                $correosbbc,
                $arrayEnviar,
                0,
                ""
            );

            // Se actualiza el campo Token 
            User::where('ID', $portalUserId)->update(['REMEMBER_TOKEN' => $token]);

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            //Registro Log
            $mensaje = "Se ha recibido una solicitud de reenvío del correo de confirmación de cuenta por parte del usuario.";
            $this->registroLog($mensaje, $portalUserId);

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'ok' => '200',
                    'msjRespuesta' => "Se enviara un link nuevamente para activar tu cuenta al correo electronico: " . $this->mask_email($emailUsuario)
                ]
            );
        } else if (isset($estadoUsuario) && $estadoUsuario == 1) {

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'ok' => '200',
                    'msjRespuesta' => "La cuenta ya se encuentra activa."
                ]
            );
        }
    }

    /*
        Metodo encargado de enmascarar el correo electronico
    */
    function mask($str, $first, $last)
    {
        $len = strlen($str);
        $toShow = $first + $last;
        return substr($str, 0, $len <= $toShow ? 0 : $first) . str_repeat("*", $len - ($len <= $toShow ? 0 : $toShow)) . substr($str, $len - $last, $len <= $toShow ? 0 : $last);
    }

    /*
        Metodo encargado de enmascarar el correo electronico
    */
    function mask_email($email)
    {
        $mail_parts = explode("@", $email);
        $domain_parts = explode('.', $mail_parts[1]);

        $mail_parts[0] = $this->mask($mail_parts[0], 2, 1);
        $domain_parts[0] = $this->mask($domain_parts[0], 2, 1);
        $mail_parts[1] = implode('.', $domain_parts);

        return implode("@", $mail_parts);
    }
}
