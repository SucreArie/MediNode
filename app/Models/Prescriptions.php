<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescriptions extends Model
{
    /** @use HasFactory<\Database\Factories\PrescriptionsFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'consultation_id',
        'medicament',
        'dosage',
        'frequence',
        'duree_jours',
        'observations',
    ];
    public function consultation()
{
    return $this->belongsTo(Consultations::class);
}
}
