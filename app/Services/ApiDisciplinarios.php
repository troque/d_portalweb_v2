<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use stdClass;

class ApiDisciplinarios
{

    private $token = "";

    public function login()
    {
        try {
            $url = env("URL_DISCIPLINARIOS") . "Auth/Login";

            $datos_post['data']['type'] = 'login';
            $datos_post['data']['attributes']['user'] = env('USUARIO_DISCIPLINARIOS');
            $datos_post['data']['attributes']['password'] = env('PASSWORD_DISCIPLINARIOS');

            $response = Http::withoutVerifying()
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])->post($url, $datos_post);

            if ($response->successful()) {
                $data = $response->collect()->all();
                $this->token = $data["token"];
                $error = new stdClass;
                $error->estado = true;
                return $error;
            }
            else if($response->status() == 401){
                $error = new stdClass;
                $error->estado = false;
                $error->error = "Error 401: Falla la validacion";
                return $error;
            }
            else{
                $error = new stdClass;
                $error->estado = false;
                $error->error = "No es posible descargar el documento";
                return $error;
            }

        } catch (Exception $e) {
            $error = new stdClass;
            $error->estado = false;
            $error->error = $e->getMessage();
            return $error;
        }
    }

    public function obtenerDocumento($uuid, $esActuacion)
    {
        try {

            $url = env("URL_DISCIPLINARIOS") . "portal-web/obtener-documento";

            if($esActuacion){
                $url .= '-actuaciones';
            }

            $datos_post['data']['type'] = 'portal-web';
            $datos_post['data']['attributes']['uuid'] = $uuid;

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Content-Type' => 'application/vnd.api+json',
                ])
                ->withToken($this->token)
                ->post($url, $datos_post);

            if ($response->status() == 200 || $response->status() == 201) {
                $datos = json_decode($response->body());
                return $datos;
            }
            else {
                $error = new stdClass;
                $error->estado = false;
                $error->error = "Error: No es posible descargar el documento.";
                return $error;
            }
        } catch (Exception  $e) {
            $error = new stdClass;
            $error->estado = false;
            $error->error = $e->getMessage();
            return $error;
        }
    }
}
