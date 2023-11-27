<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DisciplinariosTrait;
use App\Http\Controllers\Traits\LogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class DownloadFileController extends Controller
{
    use DisciplinariosTrait;
    use LogTrait;

    /**
     * Metodo encargado de generar el documento en base 64 para descargarlo
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Se captura la información
        $data = $request->route()->parameters();

        // Se captura el uuid
        $uuid = $data["uuid"];

        // Se inicializa la consulta
        $resultadoDocumento = DB::select("SELECT PDN.UUID, PDN.UUID_NOTIFICACIONES, PDN.DOCUMENTO, PDN.RUTA
                                          FROM PORTAL_DOCUMENTO_NOTIFICACIONES PDN
                                          WHERE PDN.UUID_NOTIFICACIONES = '$uuid'");

        // Se concadena la ruta del documento
        $path = storage_path() . $resultadoDocumento[0]->ruta;

        $datos = $this->buscarDocumento($uuid, false);

        if($datos->error){
            // Se retorna el error
            return back()->with(
                [
                    'error' => '400',
                    'msjRespuesta' => isset($datos->msjRespuesta) ? $datos->msjRespuesta : "La ruta es invalida o no existe el archivo. " . $path,
                ]
            );
        }

        $documentoBase64 = $datos->archivo;

        $fileContents = base64_decode($documentoBase64);

        // Se declara la variable
        $nombreDocumento = date("YmdHis") . "_" . $resultadoDocumento[0]->documento;

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $nombreDocumento . '"',
        ];

        //Registro Log
        // Se captura el id del usuario
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha descargado el documento: ".$resultadoDocumento[0]->documento.".";
        $this->registroLog($mensaje, $portalUserId);

        // Retornar la respuesta de descarga
        return Response::make($fileContents, 200, $headers);
    }

    public function descargarDocumentoActuaciones($uuid)
    {
        // Se inicializa la consulta
        $resultadoDocumento = DB::select("SELECT  
                a.uuid,
                a.auto,
                aa.documento_ruta
            FROM
                actuaciones a
            INNER JOIN archivo_actuaciones aa ON a.uuid = aa.uuid_actuacion
            WHERE a.uuid = '$uuid'
            AND aa.id_tipo_archivo = 2
            ORDER BY a.created_at DESC
        ");

        // Se concadena la ruta del documento
        $path = storage_path() . $resultadoDocumento[0]->documento_ruta;

        $datos = $this->buscarDocumento($uuid, true);

        if($datos->error){
            // Se retorna el error
            return back()->with(
                [
                    'error' => '400',
                    'msjRespuesta' => isset($datos->msjRespuesta) ? $datos->msjRespuesta : "La ruta es invalida o no existe el archivo. " . $path,
                ]
            );
        }

        $documentoBase64 = $datos->archivo;

        $fileContents = base64_decode($documentoBase64);

        $extension = explode('.', $resultadoDocumento[0]->documento_ruta);
        $extension = $extension[count($extension)-1];

        // Se declara la variable
        $nombreDocumento = date("YmdHis") . "_" . $resultadoDocumento[0]->auto.'.'.$extension;

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $nombreDocumento . '"',
        ];

        //Registro Log
        // Se captura el id del usuario
        $portalUserId = Auth::user()->id;
        $mensaje = "El usuario ha descargado el documento: ".$nombreDocumento.".";
        $this->registroLog($mensaje, $portalUserId);

        // Retornar la respuesta de descarga
        return Response::make($fileContents, 200, $headers);
    }
}
