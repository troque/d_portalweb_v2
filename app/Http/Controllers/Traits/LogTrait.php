<?php

namespace App\Http\Controllers\Traits;

use App\Http\Controllers\Utils\UtilsController;
use App\Models\PortalLogModel;
use App\Services\ApiDisciplinarios;
use Illuminate\Support\Facades\Auth;

trait LogTrait
{

    /**
     * Buscar documento en disciplinarios
     */
    public static function registroLog($mensaje, $portalUserId)
    {
        // Se inicializa el controlador del Utils
        $utilsController = new UtilsController();

        // Se llama el metodo que captura la informacion del navegador
        $informacionEquipo = $utilsController->getInformacionEquipo();

        // Se inicializa el model del log
        $portalLogModel = new PortalLogModel();

        // Se inicializan los datos de la tabla
        $datosRequestPortalLog['portal_id_user'] = $portalUserId;
        $datosRequestPortalLog['detalle'] = $mensaje;
        $datosRequestPortalLog['informacion_equipo'] = json_encode($informacionEquipo);
        $datosRequestPortalLog['estado'] = true;

        // Se crea el usuario
        $portalLogModel->create($datosRequestPortalLog);
    }
}
