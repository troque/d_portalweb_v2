<?php

namespace App\Http\Controllers\Traits;

use App\Services\ApiDisciplinarios;

trait DisciplinariosTrait
{

    /**
     * Buscar documento en disciplinarios
     */
    public static function buscarDocumento($uuid, $esActuacion)
    {
        $api = new ApiDisciplinarios();
        $respuesta = $api->login();
        if($respuesta->estado == false){
            return $respuesta;
        }

        return $api->obtenerDocumento($uuid, $esActuacion);
    }
}
