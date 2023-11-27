<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class InteresadoModel extends Model
{
    use HasFactory, HasUuid;
    use SoftDeletes;

    protected $table = "interesado";

    public $timestamps = true;

    protected $fillable = [
        "id_etapa",
        "id_tipo_interesao",
        "id_tipo_sujeto_procesal",
        "tipo_documento",
        "numero_documento",
        "primer_nombre",
        "segundo_nombre",
        "primer_apellido",
        "segundo_apellido",
        "id_departamento",
        "id_ciudad",
        "direccion",
        "direccion_json",
        "id_localidad",
        "email",
        "telefono_celular",
        "telefono_fijo",
        "id_sexo",
        "id_genero",
        "id_orientacion_sexual",
        "entidad",
        "cargo",
        "tarjeta_profesional",
        "id_dependencia",
        "id_dependencia_entidad",
        "id_tipo_entidad",
        "nombre_entidad",
        "id_entidad",
        "id_funcionario",
        "id_proceso_disciplinario",
        "estado",
        "folio",
        "created_user",
        "updated_user",
        "deleted_user",
        "autorizar_envio_correo"

    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at",
        "created_user",
        "updated_user",
        "deleted_user",
    ];

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
}
