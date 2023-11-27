<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;

class DashboardController extends Controller
{

    use LogTrait;

    /**
     * Metodo encargado de retornar la vista principal de procesos disciplinarios donde se encuentra el usuario
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        //Registro Log
        // Se captura el id del usuario
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha ingresado a la pantalla de procesos disciplinarios.";
        $this->registroLog($mensaje, $portalUserId);

        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se retorna la vista
        return view('dashboard');
    }

    /**
     * Metodo encargado de mostrar la informacion de procesos disciplinarios donde se encuentra el usuario
     *
     * @return \Illuminate\View\View
     */
    public function getInformacion()
    {
        // Se captura la informacion de la session
        $usuario = Auth::user();

        // Se captura el numero y tipo de documento
        $numeroDocumento = $usuario->numero_documento;
        $tipoDocumento = $usuario->tipo_documento;

        // Se consultan los procesos disciplinarios
        $informacion = $this->consultarProcesosDisciplinariosUsuario($numeroDocumento, $tipoDocumento);
        $informacion = isset($informacion["data"]) ? $informacion["data"] : [];

        // Se retorna la informacion del datatable consultado por Ajax
        return Datatables::of($informacion)

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('radicado', function ($informacion) {

                // Se inicialiaza la variable de estilo
                $style = "color: #939393;";

                // Se construye el boton en HTML
                $btn = '<b style="' . $style . '">' . $informacion->radicado . '</b>';

                // Se retorna el boton
                return $btn;
            })

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('informacion', function ($informacion) {

                // Se construye el boton en HTML
                $btn = '<div class="row">
                                <div class="col-md-6" style="text-align: left"> <b style="color: #939393;"> Vigencia: </b> <span style="color: #939393;">' . $informacion->vigencia . '</span> </div>
                                <div class="col-md-6" style="text-align: left"> <b style="color: #939393;"> Fecha registro: </b> <span style="color: #939393;">' . $informacion->created_at . '</span> </div> </br>
                        </div>
                        <div class="row">
                                 <div class="col-md-6" style="text-align: left"> <b style="color: #939393;"> Estado: </b> <span style="color: #939393;">' . $informacion->estado . '</span> </div>
                        </div>';

                // Se retorna el boton
                return $btn;
            })

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('detalle', function ($informacion) {

                // Se inicialiaza la variable de estilo
                $style = "color: white; background-color: #00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;";

                // Se construye el boton en HTML
                $btn = '<a style="' . $style . '" href="process-details/' . $informacion->uuid . '" class="btn btn-sm"><i class="glyphicon glyphicon-edit"></i>Ver Detalle</a>';

                // Se retorna el boton
                return $btn;
            })

            // Se mapea la columna completa
            ->rawColumns(['radicado', 'detalle', 'informacion'])

            // Se retorna el query en json
            ->make(true);
    }

    private function consultarProcesosDisciplinariosUsuario($numeroDocumento, $tipoDocumento)
    {
        // Se realiza la consulta a la tabla
        $informacionProcesos = DB::select("SELECT DISTINCT PD.RADICADO, PD.VIGENCIA, TO_CHAR(PD.CREATED_AT, 'DD/MM/YYYY') AS CREATED_AT, MTP.NOMBRE, case when PD.ESTADO = 1 then 'ACTIVO' when PD.ESTADO = 2 then 'CERRADO' when PD.ESTADO = 3 then 'ARCHIVADO' else 'NO DEFINIDO' END AS estado, PD.UUID
                                           FROM PROCESO_DISCIPLINARIO PD
                                           INNER JOIN MAS_TIPO_PROCESO MTP ON MTP.ID = PD.ID_TIPO_PROCESO
                                           INNER JOIN INTERESADO I ON I.ID_PROCESO_DISCIPLINARIO = PD.UUID
                                           WHERE TIPO_DOCUMENTO = '$tipoDocumento'
                                           AND NUMERO_DOCUMENTO = '$numeroDocumento'");

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del interesado
        if (count($informacionProcesos) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "procesosDisciplinarios" => true,
                    "data" => $informacionProcesos,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "procesosDisciplinarios" => false,
                    "msjRespuesta" => "No se han encontrado registros.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }
}
