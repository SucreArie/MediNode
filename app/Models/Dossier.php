<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dossier extends Model
{
    /** @use HasFactory<\Database\Factories\DossierFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'description',
        'date',
        'patient_id',
        'medecin_id',
        'examen_id',
        'prescription_id',
        'consultation_id',
        'centre_medical_id',
    ];
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }
    public function examen()
    {
        return $this->belongsTo(Examens::class, 'examen_id');
    }
    public function prescription()
    {
        return $this->belongsTo(Prescriptions::class, 'prescription_id');
    }
    public function consultation()
    {
        return $this->belongsTo(Consultations::class, 'consultation_id');
    }
    public function centreMedical()
{
    return $this->belongsTo(Centre_medicaux::class, 'centre_medical_id');
}
}
