<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\PortalLogModel;
use App\Http\Controllers\Utils\UtilsController;

date_default_timezone_set('America/Bogota');

class NotificationsController extends Controller
{

    use LogTrait;

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {

        //Registro Log
        // Se captura el id del usuario
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha ingresado a la pantalla de notificaciones.";
        $this->registroLog($mensaje, $portalUserId);

        // Se guarda la ejecucion con un commit para que se ejecute
        DB::connection()->commit();

        // Se retorna la vista de las notificaciones
        return view('auth.notificaciones');
    }

    public function getInformacion()
    {

        // Se captura la informacion de la session
        $usuario = Auth::user();

        // Se captura el numero y tipo de documento
        $numeroDocumento = $usuario->numero_documento;
        $tipoDocumento = $usuario->tipo_documento;

        // Se consultan los procesos disciplinarios
        $informacion = $this->consultarNotificaciones($numeroDocumento, $tipoDocumento);
        $informacion = isset($informacion["data"]) ? $informacion["data"] : [];

        // Se retorna la informacion del datatable consultado por Ajax
        return Datatables::of($informacion)

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('fecha_notificacion', function ($informacion) {

                // Se inicialiaza la variable de estilo
                $style = "color: #939393;";

                // Se construye el boton en HTML
                $btn = '<b style="' . $style . '">' . $informacion->fecha_notificacion . '</b>';

                // Se retorna el boton
                return $btn;
            })

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('informacion', function ($informacion) {

                // Se subtrae para no mostrar toda la información
                $informacionDetalleCompleta = $informacion->detalle;
                $informacionDetalle = substr($informacion->detalle, 0, 150);

                // Se construye el boton en HTML
                $btnVer = '<button title="Ver detalle" data-toggle="modal" data-target="#modalDetalle" data-detalle="' . $informacionDetalleCompleta . '"> <span class="fa fa-search" /> <button>';

                // Se construye el boton en HTML
                $btn = '<div class="row">
                            <div class="col-md-6" style="text-align: left; margin-top: 5px;"> <b style="color: #939393;"> Detalle: </b> <span style="color: #939393;"> ' . $informacionDetalle . "... " . $btnVer . '</span> </div>
                            <div class="col-md-6" style="text-align: left; margin-top: 5px;"> <b style="color: #939393;"> N° Radicado: </b> <span style="color: #939393;">' . $informacion->radicado . '</span> </div>
                            <div class="col-md-6" style="text-align: left; margin-top: 5px;"> <b style="color: #939393;"> Vigencia: </b> <span style="color: #939393;"> ' . $informacion->vigencia  . '</span> </div>
                            <div class="col-md-6" style="text-align: left; margin-top: 5px;"> <b style="color: #939393;"> Fecha proceso: </b> <span style="color: #939393;">' . $informacion->fecha_proceso_disciplinario . ' </span> </div>
                            <div class="col-md-6" style="text-align: left; margin-top: 5px;"> <b style="color: #939393;"> Tipo proceso: </b> <span style="color: #939393;">' . $informacion->nombre . ' </span> </div>
                        </div>';

                // Se retorna el boton
                return $btn;
            })

            // Se añade una nueva columna a la tabla desde el backend
            ->addColumn('detalle', function ($informacion) {

                // Se inicialiaza la variable de estilo
                $style = "color: white; background-color: #00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;";

                // Se inicialiaza la variable de estilo
                $link = "/download/" . $informacion->uuid;

                // Se construye el boton en HTML
                $btn = '<a style="' . $style . '" href="' . $link . '" class="btn btn-sm"><i class="glyphicon glyphicon-edit"></i>Descargar</a>';

                // Se retorna el boton
                return $btn;
            })

            // Se mapea la columna completa
            ->rawColumns(['fecha_notificacion', 'detalle', 'informacion'])

            // Se retorna el query en json
            ->make(true);
    }

    private function consultarNotificaciones($numeroDocumento, $tipoDocumento)
    {

        // Se realiza la consulta a la tabla
        $informacionNotificaciones = DB::select("SELECT P.UUID, P.CREATED_AT AS FECHA_NOTIFICACION, P.DETALLE, PD.RADICADO, PD.VIGENCIA, TO_CHAR(PD.CREATED_AT, 'DD/MM/YYYY') AS FECHA_PROCESO_DISCIPLINARIO, MTP.NOMBRE, case when PD.ESTADO = 1 then 'ACTIVO' when PD.ESTADO = 0 then 'INACTIVO' else 'NO DEFINIDO' END AS estado
                                                 FROM PORTAL_NOTIFICACIONES P
                                                 INNER JOIN PROCESO_DISCIPLINARIO PD ON PD.UUID = P.UUID_PROCESO_DISCIPLINARIO
                                                 INNER JOIN MAS_TIPO_PROCESO MTP ON MTP.ID = PD.ID_TIPO_PROCESO
                                                 WHERE P.NUMERO_DOCUMENTO = '$numeroDocumento'
                                                 AND P.TIPO_DOCUMENTO = '$tipoDocumento'
                                                 AND p.estado = '1'");

        // Se inicializa el array
        $array = [];

        // Se valida que haya encontrado informacion del interesado
        if (count($informacionNotificaciones) > 0) {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "notificaciones" => true,
                    "data" => $informacionNotificaciones,
                ]
            );
        } else {

            // Se añade la informacion a retornar en el array
            array_push(
                $array,
                [
                    "notificaciones" => false,
                    "msjRespuesta" => "No se han encontrado registros.",
                ]
            );
        }

        // Se retorna el resultado
        return $array[0];
    }
}
