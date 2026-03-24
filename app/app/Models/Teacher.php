<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cpf',
        'telefone',
        'categorias_ensino',
        'turnos_disponiveis',
    ];

    protected $casts = [
        'categorias_ensino' => 'array',
        'turnos_disponiveis' => 'array',
    ];
}
