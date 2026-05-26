<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centre_medicaux extends Model
{
    /** @use HasFactory<\Database\Factories\CentreMedicauxFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'ville',
        'latitude',
        'longitude',
        'gps_capacite',
    ];

    public function consultations()
    {
        return $this->hasMany(Consultations::class, 'centre_medical_id');
    }

    public function dossiers()
    {
        return $this->hasMany(Dossier::class, 'centre_medical_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'centre_medical_id');
    }
}
