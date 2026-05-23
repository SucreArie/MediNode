<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examens extends Model
{
    /** @use HasFactory<\Database\Factories\ExamensFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'consultation_id',
        'type_examen',
        'laboratoire',
        'urgence',
        'resultat',
        'date_resultat',
        'fichier_joint',
    ];
    public function consultation()
{
    return $this->belongsTo(Consultations::class);
}
}
