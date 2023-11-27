<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;
use Yajra\DataTables\DataTables;

date_default_timezone_set('America/Bogota');

class ProcessDetailsController extends Controller
{

    use LogTrait;

    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        // Se capturan los parametrosde la url
        $parametrosUrl = $request->route()->parameters();
        $uuidProcesoDisciplinario = isset($parametrosUrl["uuid"]) ? $parametrosUrl["uuid"] : "";

        // Se valida cuando el proceso viene vacio
        if (empty($uuidProcesoDisciplinario)) {

            // Se redirige al dashboard
            return redirect()->route('/dashboard');
        }

        // Se captura la informacion de usuario
        $informacionUsuario = Auth::User();
        $numeroDocumento = $informacionUsuario->numero_documento;
        $tipoDocumento = $informacionUsuario->tipo_documento;

        // Se consulta si el usuario tiene permisos para consultar la informacion de los interesados
        $informacionPermisoUsuario = $this->consultarPermisosUsuarioInteresados($numeroDocumento, $tipoDocumento, $uuidProcesoDisciplinario);

        // Se consulta la informacion del proceso disciplinario
        $informacionProcesoDisciplinario = $this->consultarInformacionProcesoDisciplinario($uuidProcesoDisciplinario);

        // Se consulta la informacion de los interesados del proceso disciplinario
        $informacionInteresados = $this->consultarInformacionInteresados($uuidProcesoDisciplinario);

        // Se consulta la informacion de las actuaciones del proceso disciplinario
        $informacionActuaciones = $this->consultarInformacionActuaciones($uuidProcesoDisciplinario, $numeroDocumento);

        $radicado = $informacionProcesoDisciplinario['datos']->radicado;

        //Registro Log

        // Se captura el id del usuario
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha visto el detalle del proceso: $radicado.";
        $this->registroLog($mensaje, $portalUserId);    

        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se devuelve la vista
        return view(
            'auth.process-details',
            [
                "informacion" => $informacionProcesoDisciplinario["datos"],
                "informacionPermisoUsuario" => $informacionPermisoUsuario["permisoUsuario"],
                "informacionInteresados" => $informacionInteresados["datos"],
                "informacionActuaciones" => $informacionActuaciones["datos"],
                "informacionDataActuaciones" => isset($informacionActuaciones["informacion"]) && $informacionActuaciones["informacion"] == true ? 1 : 0,
            ]
        );
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
    }

    /**
     * Método encargado de consultar la informacion del proceso disciplinario
     * con el uuid
     */
    public function consultarPermisosUsuarioInteresados($numeroDocumento, $tipoDocumento, $uuid)
    {
        // Se realiza la consulta a la tabla
        $results = DB::select("SELECT ID_TIPO_INTERESAO, ID_TIPO_SUJETO_PROCESAL, TO_CHAR(CREATED_AT, 'DD/MM/YYYY') AS CREATED_AT
                               FROM INTERESADO
                               WHERE NUMERO_DOCUMENTO = '$numeroDocumento'
                               AND TIPO_DOCUMENTO = '$tipoDocumento'
                               AND EMAIL IS NOT NULL
                               AND ID_PROCESO_DISCIPLINARIO = '$uuid'");

        // Se inicializa el array
        $array = [];
        $permisoUsuario = false;

        error_log("results -> " . json_encode($results));

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se captura el tipo del sujeto procesal
            $idTipoSujetoProcesal = empty($results[0]->id_tipo_sujeto_procesal) ? 0 : $results[0]->id_tipo_sujeto_procesal;
            $idTipoInteresado = empty($results[0]->id_tipo_interesao) ? 0 : $results[0]->id_tipo_interesao;

            // Se realiza la consulta a la tabla para validar si tiene permisos a la informacion
            $consultarPermisos = DB::select("SELECT PCTI.PERMISO_CONSULTA
                                             FROM PORTAL_CONFIGURACION_TIPO_INTERESADO PCTI
                                             INNER JOIN INTERESADO I ON I.ID_TIPO_SUJETO_PROCESAL = PCTI.ID_TIPO_SUJETO_PROCESAL
                                             WHERE PCTI.ID_TIPO_SUJETO_PROCESAL = '$idTipoSujetoProcesal'
                                               AND PCTI.ID_TIPO_INTERESADO = '$idTipoInteresado'");

            // Se captura si el usuario tiene permisos
            $permisoConsulta = isset($consultarPermisos[0]->permiso_consulta) && $consultarPermisos[0]->permiso_consulta == 1 ? true : false;
            $permisoUsuario = $permisoConsulta;

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "permisoUsuario" => $permisoUsuario,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "permisoUsuario" => $permisoUsuario,
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar la informacion del proceso disciplinario
     * con el uuid
     */
    public function consultarInformacionProcesoDisciplinario($uuid)
    {
        // Se realiza la consulta a la tabla
        $results = DB::select("
            SELECT
                                PD.RADICADO,
                PD.VIGENCIA,
                CONCAT(SUBSTR(INITCAP(A.DESCRIPCION), 1, 1), LOWER(SUBSTR(A.DESCRIPCION, 2))) AS DESCRIPCION,
                TO_CHAR(PD.CREATED_AT, 'DD/MM/YYYY') AS FECHA_REGISTRO,
                TO_CHAR(A.FECHA_REGISTRO, 'DD/MM/YYYY') AS FECHA_INGRESO,
                CONCAT(SUBSTR(INITCAP(ME.NOMBRE), 1, 1), LOWER(SUBSTR(ME.NOMBRE, 2))) AS ETAPA,
                CONCAT(SUBSTR(INITCAP(MDO.NOMBRE), 1, 1), LOWER(SUBSTR(MDO.NOMBRE, 2))) AS NOMBRE_DEPENDENCIA
            FROM
                PROCESO_DISCIPLINARIO PD
            INNER JOIN ANTECEDENTE A ON A.ID_PROCESO_DISCIPLINARIO = PD.UUID
            INNER JOIN MAS_ETAPA ME ON ME.ID = PD.ID_ETAPA
            INNER JOIN MAS_DEPENDENCIA_ORIGEN MDO ON MDO.ID = PD.ID_DEPENDENCIA
            WHERE PD.UUID = '$uuid'
            ORDER BY(A.CREATED_AT) DESC
            "
        );

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => true,
                    "datos" => $results[0],
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => false,
                    "datos" => [],
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar la informacion de los interesados del proceso disciplinario
     * con el uuid
     */
    public function consultarInformacionInteresados($uuid)
    {
        // Se realiza la consulta a la tabla
        $results = DB::select("SELECT MTD.NOMBRE, I.NUMERO_DOCUMENTO, I.PRIMER_NOMBRE || ' ' || I.SEGUNDO_NOMBRE || ' ' || I.PRIMER_APELLIDO || ' ' || I.SEGUNDO_APELLIDO AS NOMBRE_INTERESADO, MTSP.NOMBRE AS TIPO_INTERESADO
                               FROM INTERESADO I
                               INNER JOIN MAS_TIPO_DOCUMENTO MTD ON MTD.ID = I.TIPO_DOCUMENTO
                               INNER JOIN MAS_TIPO_SUJETO_PROCESAL MTSP ON MTSP.ID = I.ID_TIPO_SUJETO_PROCESAL
                               WHERE I.ID_PROCESO_DISCIPLINARIO = '$uuid'
                               AND I.ESTADO = 1");

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => true,
                    "datos" => $results,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => false,
                    "datos" => [],
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }

    /**
     * Método encargado de consultar la informacion de las actuaciones del proceso disciplinario
     * con el uuid
     */
    public function consultarInformacionActuaciones($uuid, $numeroDocumento)
    {
        // Se realiza la consulta a la tabla
        $results = DB::select("SELECT A.UUID, TO_CHAR(A.CREATED_AT, 'DD/MM/YYYY') AS CREATED_AT, MA.NOMBRE_ACTUACION, A.AUTO, A.DOCUMENTO_RUTA
                               FROM ACTUACIONES A
                               INNER JOIN MAS_ACTUACIONES MA ON MA.ID = A.ID_ACTUACION
                               INNER JOIN PORTAL_NOTIFICACIONES PN ON A.UUID = PN.ID_ACTUACION
                               WHERE A.UUID_PROCESO_DISCIPLINARIO = '$uuid'
                               AND A.ID_ESTADO_ACTUACION IN (5)
                               AND MA.VISIBLE = 1
                               AND PN.NUMERO_DOCUMENTO = $numeroDocumento");

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del interesado
        if (count($results) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => true,
                    "datos" => $results,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "informacion" => false,
                    "datos" => [],
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }
}
