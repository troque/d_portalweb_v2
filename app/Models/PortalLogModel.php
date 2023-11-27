<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;

class PortalLogModel extends Model
{
    use HasFactory, HasUuid;

    protected $table = "portal_log";

    public $timestamps = true;

    protected $fillable = [
        "uuid",
        "portal_id_user",
        "detalle",
        "informacion_equipo",
        "estado",
        "created_user",
        "updated_user",
        "delete_user",
        "created_at",
        "updated_at",
    ];

    protected $hidden = [
        "created_user",
        "updated_user",
        "deleted_user",
        "updated_at",
    ];

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
}