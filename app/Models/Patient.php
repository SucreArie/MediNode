<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'phone',
        'birthDate',
        'gender',
        'address',
        'city',
        'postalCode',
        'bloodType',
        'allergies',
        'emergencyName',
        'emergencyPhone',
        'insuranceId',
        'condition',
        'notes',
        'status',
        'centre_medical_id',
    ];
    public function centreMedical()
{
    return $this->belongsTo(Centre_medicaux::class, 'centre_medical_id');

}
}
