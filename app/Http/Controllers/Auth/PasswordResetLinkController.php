<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Traits\LogTrait;
use App\Http\Controllers\Traits\MailTrait;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Support\Facades\DB;
use App\Models\PortalTokenModel;

date_default_timezone_set('America/Bogota');

class PasswordResetLinkController extends Controller
{

    use LogTrait;

    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
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

        // Se inicializa la clase del usuario
        $userController = new RegisteredUserController();

        // Se consulta la informacion del usuario de la tabla de interesados
        $informacionUsuarioInteresado = $userController->consultarInformacionInteresado($numeroDocumento, $tipoDocumento);
        $existeUsuario = $informacionUsuarioInteresado["interesado"];

        // Se valida que exista un usuario
        if (isset($existeUsuario) && $existeUsuario == 1) {

            // Se captura la informacion del usuario
            $datosUsuario = $informacionUsuarioInteresado["datos"];
            $correoUsuario = $datosUsuario->email;

            // Se valida que el usuario tenga correo electronico
            if (isset($correoUsuario)) {

                // Se inicializa la clase
                $registerUserController = new RegisteredUserController();

                // Se consulta la informacion del usuario
                $informacionUsuario = $registerUserController->consultarInformacionUsuario($numeroDocumento, $tipoDocumento);
                if($informacionUsuario['existeUsuario'] == false){
                    // Se retorna el mensaje 
                    return back()->withErrors(
                        [
                            'inactivo' => 'error',
                            'msjRespuesta' => $informacionUsuario['msjRespuesta'],
                        ]
                    );
                }

                $existeUsuario = $informacionUsuario["existeUsuario"];
                $estado = $informacionUsuario["estado"];

                // Se valida que el usuario este activo para poder enviar una nueva contraseña
                if (isset($estado) && $estado == 1) {

                    // Se captura el id del usuario
                    $usuarioId = $informacionUsuario["datos"]->id;

                    // Se captura la informacion
                    $correos = [$correoUsuario];
                    $primerNombre = isset($datosUsuario->primer_nombre) ? strtoupper(trim($datosUsuario->primer_nombre)) . " " : "";
                    $segundoNombre = isset($datosUsuario->segundo_nombre) ? strtoupper(trim($datosUsuario->segundo_nombre)) . " " : "";
                    $primerApellido = isset($datosUsuario->primer_apellido) ? strtoupper(trim($datosUsuario->primer_apellido)) . " " : "";
                    $segundoApellido = isset($datosUsuario->segundo_apellido) ? strtoupper(trim($datosUsuario->segundo_apellido)) . " " : "";
                    $nombre_usuario = $primerNombre . $segundoNombre . $primerApellido . $segundoApellido;
                    $asunto = "PORTAL WEB - RESTABLECER CONTRASEÑA";
                    $contenido = "PORTAL WEB - RESTABLECER CONTRASEÑA";
                    $archivos = null;
                    $correoscc = null;
                    $correosbbc = null;
                    $token = Str::random(90);
                    $arrayEnviar = [
                        "rutaVerificarCorreo" => env("APP_URL") . '/reset-password/' . $token . '/' . $usuarioId
                    ];

                    // Se encripta el string
                    $stringEncriptar = date("YmdHis");

                    // Se inicializa la clase para encriptar la contraseña
                    $crypt = Hash::make($stringEncriptar);

                    // Se procede a enviar el correo electronico
                    MailTrait::sendMail(
                        $correos,
                        $nombre_usuario,
                        $asunto,
                        $contenido,
                        $archivos,
                        $correoscc,
                        $correosbbc,
                        $arrayEnviar,
                        1,
                        $stringEncriptar
                    );

                    // Se procede a actualizar la contraseña del usuario en la tabla portal users
                    User::where('ID', $usuarioId)->update(
                        [
                            "remember_token" => $token,
                        ]
                    );                    

                    // Se inicializa la clase token users
                    $portalTokenModel = new PortalTokenModel();

                    // Se inicializan los datos de la tabla
                    $datosRequestPortalUsers['portal_id_user'] = $usuarioId;
                    $datosRequestPortalUsers['token'] = $token;
                    $datosRequestPortalUsers['expire_time'] = env("APP_EXPIRE_TIME"); // Se calculan 300 segundos o 5 minutos para el tiempo de expiración

                    // Se crea el usuario
                    $portalTokenModel->create($datosRequestPortalUsers);

                    // Se guarda la ejecucion con un commit para que se ejecute
                    DB::connection()->commit();

                    //Registro Log
                    $mensaje = "Se ha generado el token: '$token' para generar una nueva contraseña.";
                    $this->registroLog($mensaje, $usuarioId);

                    // Se guarda la ejecucion con un commit para que se ejecute
                    DB::connection()->commit();

                    // Se retorna el mensaje 
                    return back()->withErrors(
                        [
                            'ok' => '200',
                            'msjRespuesta' => "Se ha enviado un link al correo electronico para restablecer la contraseña.",
                        ]
                    );
                } else {

                    // Se retorna el mensaje 
                    return back()->withErrors(
                        [
                            'inactivo' => 'error',
                            'msjRespuesta' => "El usuario no ha activado la cuenta, por favor active la cuenta para poder restablecer la contraseña.",
                        ]
                    );
                }
            } else {

                // Se retorna el mensaje 
                return back()->withErrors(
                    [
                        'error' => '400',
                        'msjRespuesta' => "El usuario registrado no tiene correo electronico por favor actualize la información.",
                    ]
                );
            }
        } else {

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'error' => '400',
                    'msjRespuesta' => "No se encuentra información de este usuario en la base de datos.",
                ]
            );
        }
    }
}
