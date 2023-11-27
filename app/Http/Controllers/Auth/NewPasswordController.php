<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PortalTokenModel;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;

date_default_timezone_set('America/Bogota');

use DateTime;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Se captura el token
        $parametrosUrl = $request->route()->parameters();
        $token = isset($parametrosUrl["token"]) ? $parametrosUrl["token"] : "";
        $userId = isset($parametrosUrl["userId"]) ? $parametrosUrl["userId"] : false;

        // Se validan cuando se va a registrar una nueva contraseña o se va a actualizar
        /**
         * 1. False -> Nueva contraseña
         * 2. True  -> Actualizar contraseña
         */
        if (!$userId) {

            // Se consulta la informacion del usuario con el token
            $informacionUsuario = $this->consultarInformacionUsuario($token);

            // Se captura el estado actual del usuario
            $estadoUsuario = isset($informacionUsuario["datos"]->estado) ? $informacionUsuario["datos"]->estado : "";

            // Se valida que el usuario este activo para redirigirlo a otra vista
            if (isset($estadoUsuario) && $estadoUsuario == 1) {

                // Se retorna a otra vista
                return view("auth.token-verified");
            }

            // Se captura el resto de informacion del usuario
            $numeroDocumento = $informacionUsuario["datos"]->numero_documento;
            $tipoDocumento = $informacionUsuario["datos"]->tipo_documento;
            $mensajeCedula = $informacionUsuario["mensajeCedula"];

            // Se devuelve la vista con la informacion
            return view('auth.reset-password', [
                'request' => $request,
                'numeroDocumento' => $numeroDocumento,
                'tipoDocumento' => $tipoDocumento,
                'mensajeCedula' => $mensajeCedula,
            ]);
        } else if (!empty($userId)) {

            // Se consulta la informacion del usuario con el token
            $informacionUsuario = $this->consultarInformacionUsuarioActualizar($token, 1);

            // Se valida si hubo error
            if (isset($informacionUsuario["error"])) {

                // Se redirige al inicio con el mensaje de error
                return redirect()
                    ->route("login")
                    ->withErrors(
                        [
                            'error' => '400',
                            'msjRespuesta' => $informacionUsuario["error"],
                        ]
                    );
            }

            // Se captura el resto de informacion del usuario
            $numeroDocumento = $informacionUsuario["datos"]->numero_documento;
            $tipoDocumento = $informacionUsuario["datos"]->tipo_documento;
            $mensajeCedula = $informacionUsuario["mensajeCedula"];

            // Se devuelve la vista con la informacion
            return view('auth.reset-password', [
                'request' => $request,
                'numeroDocumento' => $numeroDocumento,
                'tipoDocumento' => $tipoDocumento,
                'mensajeCedula' => $mensajeCedula,
                'actualizar' => 1
            ]);
        }
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Se captura la informacion de la peticion
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;
        $token = $request->token;
        $userId = isset($request->userId) ? true : false;

        // Se valida cuando hay un userId
        /**
         * 1. userId = true  -> Actualizar
         * 2. userId = false -> Nueva contraseña
         */
        if (!$userId) {

            // Se consulta la informacion del usuario
            $informacionUsuario = $this->consultarInformacionUsuarioGeneral($token);

            // Se captura el id del usuario
            $usuarioId = $informacionUsuario["datos"]->id;

            // Se validan los campos
            $request->validate([
                'token' => ['required'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()->letters()->mixedCase()->numbers()],
            ]);

            // Se inicializa la clase para encriptar la contraseña
            $crypt = Hash::make($password);

            // Se actualiza la contraseña, el estado activo y se borra el token
            $actualizarUsuario = User::where('ID', $usuarioId)->update(
                [
                    "estado" => 1,
                    "password" => $crypt,
                    "email_verified_at" => date("Y-m-d H:i:s"),
                ]
            );

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            // Se valida que se haya actualizado
            if ($actualizarUsuario == 1) {

                // Se retorna a la vista 
                return view('auth.user-confirmation');
            } else {

                // Se retorna a la vista 
                return view('auth.login');
            }
        } else if ($userId) {

            // Se consulta la informacion del usuario
            $informacionUsuario = $this->consultarInformacionUsuarioGeneral($token);

            // Se validan los campos
            $request->validate([
                'token' => ['required'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()->letters()->mixedCase()->numbers()],
            ]);

            // Se inicializa la clase para encriptar la contraseña
            $crypt = Hash::make($password);

            // Se captura el id del usuario
            $usuarioId = isset($request->userId) ? $request->userId : "";

            // Se actualiza la contraseña, el estado activo y se borra el token
            $actualizarUsuario = User::where('ID', $usuarioId)->update(
                [
                    "password" => $crypt,
                ]
            );

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            //Registro Log
            $mensaje = "El usuario ha actualizado la contraseña con éxito.";
            $this->registroLog($mensaje, $usuarioId);            

            // Se redirige al inicio con el mensaje de error
            return redirect()
                ->route("login")
                ->withErrors(
                    [
                        'ok' => '200',
                        'msjRespuesta' => "La contraseña ha sido actualizada con éxito."
                    ]
                );
        }
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de usuarios portal con
     *  el token
     */
    public function consultarInformacionUsuarioActualizar($token, $actualizarContrasena = 0)
    {
        // Se inicializa el modelo de datos del usuario
        $userModel = new User();

        // Se consultan el usuario con el token
        $results = $userModel::where('REMEMBER_TOKEN', $token)
            ->get();

        // Se inicializa el array
        $array = [];
        $mensajeCedula = "";

        // Se consulta que el codigo de acceso sea el mismo con el token
        $portalTokenModel = new PortalTokenModel();

        // Se captura la informacion del usuario
        $portalUserId = isset($results[0]->id) ? $results[0]->id : null;

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se consultan el usuario con el token
            $informacionCodigo = $portalTokenModel::where('portal_id_user', $portalUserId)
                ->where('token', $token)
                ->where('estado', 1)
                ->first();

            // Se valida que haya informacion con ese codigo
            if (empty($informacionCodigo)) {

                // Se añade la informacion a retornar en el array
                array_push(
                    $array,
                    [
                        "error" => "El token no existe o no es valido.",
                    ]
                );

                // Se retorna
                return $array[0];
            }

            // Se captura la fecha principal
            $fechaPrincipal = $informacionCodigo->created_at;
            $fechaActual = new DateTime(date("Y-m-d H:i:s"));

            // Se capturan el tiempo maximo de expiracion
            $tiempoExpiracion = $informacionCodigo->expire_time;

            // Se captura la diferencia de fechas
            $diferenciaFechas = date_diff($fechaPrincipal, $fechaActual);

            // Se capturan los minutos
            $segundos = $diferenciaFechas->days * 24 * 60;
            $segundos += $diferenciaFechas->h * 60;
            $segundos += $diferenciaFechas->i * 60;

            // Se valida si el token excedio el tiempo limite
            if ($segundos > $tiempoExpiracion) {

                // Se añade la informacion a retornar en el array
                array_push(
                    $array,
                    [
                        "error" => "El token o código de verificación excedio el tiempo máximo.",
                    ]
                );

                // Se retorna
                return $array[0];
            }

            // Se valida el tipo de cedula
            if ($results[0]->tipo_documento == 1) {

                // Se devuelve el valor
                $mensajeCedula = "Cédula de Ciudadanía";
            } else if ($results[0]->tipo_documento == 2) {

                // Se devuelve el valor
                $mensajeCedula = "Cédula de Extranjería";
            } else if ($results[0]->tipo_documento == 3) {

                // Se devuelve el valor
                $mensajeCedula = "Pasaporte";
            } else {

                // Se devuelve el valor
                $mensajeCedula = "No informa";
            }

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "datos" => $results[0],
                    "mensajeCedula" => $mensajeCedula
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "error" => "El token no existe o no es valido por favor genere un nuevo token.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de usuarios portal con
     *  el token
     */
    public function consultarInformacionUsuario($token, $actualizarContrasena = 0)
    {
        // Se inicializa el modelo de datos del usuario
        $userModel = new User();

        // Se consultan el usuario con el token
        $results = $userModel::where('REMEMBER_TOKEN', $token)
            ->get();

        // Se inicializa el array
        $array = [];
        $mensajeCedula = "";

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se valida que el usuario este inactivo para activar
            if (isset($results[0]->estado) && ($results[0]->estado == 0)) {

                // Se valida el tipo de cedula
                if ($results[0]->tipo_documento == 1) {

                    // Se devuelve el valor
                    $mensajeCedula = "Cédula de Ciudadanía";
                } else if ($results[0]->tipo_documento == 2) {

                    // Se devuelve el valor
                    $mensajeCedula = "Cédula de Extranjería";
                } else if ($results[0]->tipo_documento == 3) {

                    // Se devuelve el valor
                    $mensajeCedula = "Pasaporte";
                } else {

                    // Se devuelve el valor
                    $mensajeCedula = "No informa";
                }

                // Se añade la informacion a retornar en el array
                array_push(
                    $array,
                    [
                        "users" => "ok",
                        "msjRespuesta" => "El usuario se encuentra inactivo y el token no ha sido activado.",
                        "datos" => $results[0],
                        "mensajeCedula" => $mensajeCedula
                    ]
                );
            } else if (isset($results[0]->estado) && ($results[0]->estado == 1)) {

                // Se añade la informacion a retornar en el array
                array_push(
                    $array,
                    [
                        "users" => "ok",
                        "msjRespuesta" => "El usuario ya se encuentra activo y el token ya ha sido activado.",
                        "datos" => $results[0],
                        "mensajeCedula" => $mensajeCedula
                    ]
                );
            }
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "interesado" => false,
                    "msjRespuesta" => "Ha ocurrido un error al validar el token",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de usuarios portal con
     *  el token
     */
    public function consultarInformacionUsuarioGeneral($token)
    {
        // Se inicializa el modelo de datos del usuario
        $userModel = new User();

        // Se consultan el usuario con el token
        $results = $userModel::where('REMEMBER_TOKEN', $token)
            ->get();

        // Se inicializa el array
        $array = [];
        $mensajeCedula = "";

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se valida el tipo de cedula
            if ($results[0]->tipo_documento == 1) {

                // Se devuelve el valor
                $mensajeCedula = "Cédula de Ciudadanía";
            } else if ($results[0]->tipo_documento == 2) {

                // Se devuelve el valor
                $mensajeCedula = "Cédula de Extranjería";
            } else if ($results[0]->tipo_documento == 3) {

                // Se devuelve el valor
                $mensajeCedula = "Pasaporte";
            } else {

                // Se devuelve el valor
                $mensajeCedula = "No informa";
            }

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "users" => "ok",
                    "msjRespuesta" => "Se encontró el usuario",
                    "datos" => $results[0],
                    "mensajeCedula" => $mensajeCedula
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "interesado" => false,
                    "msjRespuesta" => "No se ha encontrado ningun usuario",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }
}
