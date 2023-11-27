<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InteresadoModel;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\MailTrait;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    use MailTrait;

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
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
        $informacionUsuario = $this->consultarInformacionUsuario($numeroDocumento, $tipoDocumento);
        $existeUsuario = $informacionUsuario["existeUsuario"];

        // Se valida si existe un usuario con esta informacion para retornar error
        if (isset($existeUsuario) && $existeUsuario) {

            // Se retorna el mensaje
            return back()->withErrors(
                [
                    'error' => '400',
                    'msjRespuesta' => "Ha ocurrido un error, ya se encuentra un usuario registrado con esta información.",
                ]
            );
        }

        // Se consulta con la tabla de interesados que exista un registro con ese numero y tipo de documento
        $validarUsuario = $this->consultarInformacionInteresado($numeroDocumento, $tipoDocumento, 1);
        $existeInteresado = $validarUsuario["interesado"];
        $msjRespuesta = $validarUsuario["msjRespuesta"];

        // Se valida que exista el usuario y tenga correo electronico
        if (isset($existeInteresado) && $existeInteresado == true) {

            // Se captura la informacion
            $correos = [$validarUsuario["email"]];
            $primerNombre = isset($validarUsuario["datos"]->primer_nombre) ? strtoupper(trim($validarUsuario["datos"]->primer_nombre)) : "";
            $segundoNombre = isset($validarUsuario["datos"]->segundo_nombre) ? strtoupper(trim($validarUsuario["datos"]->segundo_nombre)) : "";
            $primerApellido = isset($validarUsuario["datos"]->primer_apellido) ? strtoupper(trim($validarUsuario["datos"]->primer_apellido)) : "";
            $segundoApellido = isset($validarUsuario["datos"]->segundo_apellido) ? strtoupper(trim($validarUsuario["datos"]->segundo_apellido)) : "";
            $nombre_usuario = $primerNombre . " " . $segundoNombre .  " " . $primerApellido . " " . $segundoApellido;
            $asunto = "PORTAL WEB - REGISTRO DE USUARIO";
            $contenido = "PORTAL WEB - REGISTRO DE USUARIO";
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
                $nombre_usuario,
                $asunto,
                $contenido,
                $archivos,
                $correoscc,
                $correosbbc,
                $arrayEnviar,
                0,
                ""
            );

            // Se inicializa el array para crear el usuario
            $datosRequestUsers = [
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $numeroDocumento,
                'estado' => false,
                'email' => $validarUsuario["email"],
                'remember_token' => $token,
            ];

            // Se inicializa la clase users
            $userModel = new User();

            // Se crea el usuario
            $userModel->create($datosRequestUsers);

            // Se guarda la ejecucion con un commit para que se ejecute
            DB::connection()->commit();

            // Se retorna el mensaje
            return back()->withErrors(
                [
                    'ok' => '200',
                    'msjRespuesta' => __($msjRespuesta),
                ]
            );
        } else {

            // Se retorna el mensaje
            return back()->withErrors(
                [
                    'error' => '400',
                    'msjRespuesta' => __($msjRespuesta),
                ]
            );
        }
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de interesados con
     * el número y tipo de documento
     *
     */
    public function consultarInformacionInteresado($numeroDocumento, $tipoDocumento, $registroPrincipal = 0)
    {
        // Se inicializa el modelo de datos del interesado
        $interesadoModel = new InteresadoModel();

        // Se consultan el usuario con el token
        $results = $interesadoModel::where('TIPO_DOCUMENTO', $tipoDocumento)
        ->where('NUMERO_DOCUMENTO', $numeroDocumento)
        ->where('AUTORIZAR_ENVIO_CORREO', 1)
        ->orderByDesc('CREATED_AT')
        ->get();

        // Se inicializa el array
        $array = [];
        $email = "";

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se recorre el array
            foreach ($results as $key => $value) {

                // Se captura la informacion
                $correoInteresado = isset($value->email) ? $value->email : "";

                // Se valida que exista un correo
                if (!empty($correoInteresado)) {

                    // Se redeclara la variable
                    $email = $correoInteresado;

                    // Se valida que solo sea cuando se registre un usuario nuevo
                    if ($registroPrincipal == 1) {

                        // Se busca si existe un usuario registrado con este correo electronico
                        $buscarUsuarioCorreoExistente = $this->consultarInformacionUsuarioCorreo($email);

                        // Se captura la informacion
                        $existeUsuarioCorreo = isset($buscarUsuarioCorreoExistente["existeUsuario"]) ? $buscarUsuarioCorreoExistente["existeUsuario"] : "";

                        // Se valida que no existe para registrar el nuevo usuario
                        if ($existeUsuarioCorreo == 1) {

                            // Se añade la informacion a retornar en el array
                            array_push(
                                $array,
                                [
                                    "interesado" => false,
                                    "msjRespuesta" => "El correo del usuario ya se encuentra registrado en el sistema, por favor actualize la información.",
                                ]
                            );

                            // Se sale del ciclo
                            break;
                        }
                    }

                    // Se añade la informacion a retornar en el array
                    array_push(
                        $array,
                        [
                            "interesado" => true,
                            "msjRespuesta" => "Se enviara un link para finalizar el registro al correo: " . $this->mask_email($email),
                            "email" => $email,
                            "datos" => $value,
                        ]
                    );

                    // Se sale del ciclo
                    break;
                }
            }

            // Se valida cuando existe un usuario pero ninguno tiene correo electronico
            if (empty($array)) {

                // Se añade la informacion a retornar en el array
                array_push(
                    $array,
                    [
                        "interesado" => false,
                        "msjRespuesta" => "Se encontró el usuario en la base de datos del interesado pero no tiene correo electronico, por favor actualice la información.",
                    ]
                );
            }
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "interesado" => false,
                    "msjRespuesta" => "No se encontró el usuario en nuestra base de datos del interesado.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de portal usuarios con
     * el número y tipo de documento
     *
     */
    public function consultarInformacionUsuario($numeroDocumento, $tipoDocumento)
    {
        // Se incializa el modelo
        $userModel = new User();

        // Se consultan el usuario con el token
        $results = $userModel::where('TIPO_DOCUMENTO', $tipoDocumento)
            ->where('NUMERO_DOCUMENTO', $numeroDocumento)
            ->get();

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del usuario ya registrada
        if (count($results) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "existeUsuario" => true,
                    "datos" => $results[0],
                    "estado" => $results[0]->estado,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "existeUsuario" => false,
                    "error" => "400",
                    "msjRespuesta" => "No existe información de este usuario en la base de datos.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar si exista un registro en la tabla de portal usuarios con
     * el número y tipo de documento
     *
     */
    public function consultarInformacionUsuarioCorreo($correo)
    {
        // Se incializa el modelo
        $userModel = new User();

        // Se consultan el usuario con el token
        $results = $userModel::where('EMAIL', $correo)
            ->get();

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del usuario ya registrada
        if (count($results) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "existeUsuario" => true,
                    "datos" => $results[0],
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "existeUsuario" => false,
                    "error" => "400",
                    "msjRespuesta" => "No existe información de este usuario con el email en la base de datos.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
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
