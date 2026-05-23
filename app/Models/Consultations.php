<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultations extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultationsFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'patient_id',
        'medecin_id',
        'centre_medical_id',
        'date',
        'motif',
        'symptomes',
        'diagnostic',
        'traitement',
        'notes',
    ];

    public function patient()
{
    return $this->belongsTo(User::class, 'patient_id');
}

public function medecin()
{
    return $this->belongsTo(User::class, 'medecin_id');
}

public function centreMedical()
{
    return $this->belongsTo(Centre_medicaux::class, 'centre_medical_id');
}
public function prescriptions()
{
    return $this->hasMany(Prescriptions::class);
}

public function examens()
{
    return $this->hasMany(Examens::class);
}
}
