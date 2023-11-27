<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\MailTrait;
use App\Models\PortalTokenModel;
use App\Models\User;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;

date_default_timezone_set('America/Bogota');

class AuthenticatedSessionController extends Controller
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
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        // Se captura la informacion del usuario
        $tipoDocumento = $request->tipo_documento;
        $numeroDocumento = $request->numero_documento;
        $contraseña = $request->password;

        // Se inicializa la clase
        $userController = new RegisteredUserController();
        $informacionUsuario = $userController->consultarInformacionUsuario($numeroDocumento, $tipoDocumento);
        $existeUsuario = $informacionUsuario["existeUsuario"];
        $portalUserId = isset($informacionUsuario["datos"]->id) ? $informacionUsuario["datos"]->id : null;
        $estadoUsuario = isset($informacionUsuario["datos"]->estado) ? $informacionUsuario["datos"]->estado : null;

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

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'inactivo' => 'ok',
                    'msjRespuesta' => "El usuario no ha activado la cuenta, por favor active la cuenta para poder iniciar sesión.",
                ]
            );
        }

        //Registro Log
        $mensaje = "El usuario está en proceso de iniciar sesión.";
        $this->registroLog($mensaje, $portalUserId);
        
        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se valida que el tipo de documento, numero de documento y contraseña sean correctas
        $request->authenticate($request, 1);

        // Se validan los caracteres
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(1, 99)
            . $characters[rand(0, strlen($characters) - 1)]
            . mt_rand(1, 99)
            . $characters[rand(0, strlen($characters) - 1)]
            . mt_rand(1, 99)
            . $characters[rand(0, strlen($characters) - 1)];

        // Se mezcla el resultado
        $string = str_shuffle($pin);

        // Se genera el token
        $token = $string;

        // Se consulta la informacion del usuario en la tabla de interesados
        $informacionUsuarioGeneral = $userController->consultarInformacionInteresado($numeroDocumento, $tipoDocumento);
        $existeInteresado = $informacionUsuarioGeneral["interesado"];
        $msjRespuesta = $informacionUsuarioGeneral["msjRespuesta"];

        // Se valida que exista el usuario y tenga correo electronico
        if (isset($existeInteresado) && $existeInteresado == true) {

            // Se captura la informacion
            $correos = [$informacionUsuarioGeneral["email"]];
            $primerNombre = isset($informacionUsuarioGeneral["datos"]->primer_nombre) ? strtoupper(trim($informacionUsuarioGeneral["datos"]->primer_nombre)) : "";
            $segundoNombre = isset($informacionUsuarioGeneral["datos"]->segundo_nombre) ? strtoupper(trim($informacionUsuarioGeneral["datos"]->segundo_nombre)) : "";
            $primerApellido = isset($informacionUsuarioGeneral["datos"]->primer_apellido) ? strtoupper(trim($informacionUsuarioGeneral["datos"]->primer_apellido)) : "";
            $segundoApellido = isset($informacionUsuarioGeneral["datos"]->segundo_apellido) ? strtoupper(trim($informacionUsuarioGeneral["datos"]->segundo_apellido)) : "";
            $nombre_usuario = $primerNombre . " " . $segundoNombre .  " " . $primerApellido . " " . $segundoApellido;
            $asunto = "PORTAL WEB - CÓDIGO DE ACCESO";
            $contenido = "PORTAL WEB - CÓDIGO DE ACCESO";
            $archivos = null;
            $correoscc = null;
            $correosbbc = null;

            // Se procede a enviar el correo electronico
            MailTrait::sendMail(
                $correos,
                $nombre_usuario,
                $asunto,
                $contenido,
                $archivos,
                $correoscc,
                $correosbbc,
                [],
                2,
                $token,
            );

            // Se inicializa la clase token users
            $portalTokenModel = new PortalTokenModel();

            // Se actualiza el campo estado de todos los codigos enviados anteriormente
            PortalTokenModel::where('PORTAL_ID_USER', $portalUserId)->update(['ESTADO' => 0]);

            // Se inicializan los datos de la tabla
            $datosRequestPortalUsers['portal_id_user'] = $portalUserId;
            $datosRequestPortalUsers['token'] = $token;
            $datosRequestPortalUsers['expire_time'] = env("APP_EXPIRE_TIME"); // Se calculan 300 segundos o 5 minutos para el tiempo de expiración

            // Se crea el usuario
            $portalTokenModel->create($datosRequestPortalUsers);

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            //Registro Log
            $mensaje = "Se ha generado el código de acceso '$token'.";
            $this->registroLog($mensaje, $portalUserId);

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();
        }

        // Se genera el token a validar en la pagina o vista
        $token = Str::random(80);

        // Se captura la informacion
        $tipo_documento = $request->tipo_documento;
        $numero_documento = $request->numero_documento;
        $password = $request->password;

        // Se encripta la contraseña, tipo y numero de documento
        $contraseñaEncriptada = Crypt::encryptString($password);
        $tipoDocumentoEncriptado = Crypt::encryptString($tipo_documento);
        $numDocumentoEncriptado = Crypt::encryptString($numero_documento);

        // Se actualiza el campo Token
        User::where('ID', $portalUserId)->update(['REMEMBER_TOKEN' => $token]);

        // Se retorna la vista del controlador del codigo de accesso
        return redirect()->route(
            "code-access",
            [
                "token" => $token,
                "auth" => $contraseñaEncriptada,
                "tip" => $tipoDocumentoEncriptado,
                "ced" => $numDocumentoEncriptado,
            ]
        );
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {

        //Registro Log
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha cerrado la sesión.";
        $this->registroLog($mensaje, $portalUserId);

        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se destruye la session
        Auth::guard('web')->logout();

        // Se invalida la session
        $request->session()->invalidate();

        // Se destruye el token
        $request->session()->regenerateToken();

        // Se redirecciona al inicio
        return redirect('/login');
    }
}
