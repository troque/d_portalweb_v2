<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\LogTrait;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Models\PortalTokenModel;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Support\Facades\DB;

date_default_timezone_set('America/Bogota');

use DateTime;

class CodeAccessController extends Controller
{
    use LogTrait;

    /**
     * Metodo encargado de retornar la vista del codigo de accesso
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {

        // Se captura la contraseña, tipo y numero de documento
        $token = isset($request->token) ? $request->token : '';
        $contraseñaDesencriptada = isset($request->auth) ? Crypt::decryptString($request->auth) : '';
        $tipoDocumentoDesencriptado = isset($request->tip) ? Crypt::decryptString($request->tip) : '';
        $numDocumentoDesencriptado = isset($request->ced) ? Crypt::decryptString($request->ced) : '';

        // Se valida que haya informacion en la URL
        if (empty($token) || empty($contraseñaDesencriptada) || empty($tipoDocumentoDesencriptado) || empty($numDocumentoDesencriptado)) {

            // Se retorna al inicio
            return redirect("/login");
        }

        // Se consulta que el codigo de acceso sea el mismo con el token
        $userModel = new User();

        // Se consultan el usuario con el token
        $usuarioInformacion = $userModel::where('tipo_documento', $tipoDocumentoDesencriptado)
            ->where('numero_documento', $numDocumentoDesencriptado)
            ->where('remember_token', $token)
            ->first();

        // Se captura el id del usuario
        $portalUserId = isset($usuarioInformacion->id) ? $usuarioInformacion->id : '';

        // Se valida que haya informacion del usuario con este token
        if (empty($usuarioInformacion)) {

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'error' => '400',
                    'msjRespuesta' => "El token es invalido y/o ya ha expirado.",
                ]
            );
        }

        // Se devuelve la vista con la informacion
        return view(
            'auth.code-access',
            [
                "tipoDocumento" => $tipoDocumentoDesencriptado,
                "numeroDocumento" => $numDocumentoDesencriptado,
                "contrasena" => $contraseñaDesencriptada,
                "portalUserId" => $portalUserId,
            ]
        );
    }

    /**
     * Metodo encargado de validar el codigo de accesso y dar inicio de sessión al usuario
     */
    public function store(Request $request)
    {
        // Se valida el formulario
        $request->validate([
            'codigo_acceso' => ['required', 'string', 'max:15'],
        ]);

        // Se captura la informacion del usuario
        $codigoAcceso = $request->codigo_acceso;
        $tipo_documento = $request->tipo_documento;
        $numero_documento = $request->numero_documento;
        $contraseña = $request->password;
        $portalUserId = $request->portalUserId;

        // Se consulta que el codigo de acceso sea el mismo con el token
        $portalTokenModel = new PortalTokenModel();

        // Se consultan el usuario con el token
        $informacionCodigo = $portalTokenModel::where('portal_id_user', $portalUserId)
            ->where('token', $codigoAcceso)
            ->where('estado', 1)
            ->first();

        // Se valida que haya informacion con ese codigo
        if (empty($informacionCodigo)) {

            // Se retorna el mensaje 
            return back()->withErrors(
                [
                    'codigo_acceso' => 'El código de acceso es incorrecto.',
                ]
            );
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

            // Se redirige al inicio con el mensaje de error
            return redirect()
                ->route("login")
                ->withErrors(
                    [
                        'error' => '400',
                        'msjRespuesta' => "El código de verificación excedio el tiempo máximo.",
                    ]
                );
        }

        // Se inicializa la clase
        $requestLogin = new LoginRequest();

        // Se valida que el tipo de documento, numero de documento y contraseña sean correctas
        $requestLogin->authenticate($request);

        //Registro Log
        $mensaje = "El usuario ha iniciado sesión en el sistema.";
        $this->registroLog($mensaje, $portalUserId);        

        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se redirige al Dashboard
        return redirect()->route("dashboard");
    }

    /*
        Metodo encargado de calcular los minutos entre dos fechas

    */
    function minutosTranscurridos($fecha_i, $fecha_f)
    {
        $minutos = (strtotime($fecha_i) - strtotime($fecha_f)) / 60;
        $minutos = abs($minutos);
        $minutos = floor($minutos);
        return $minutos;
    }
}