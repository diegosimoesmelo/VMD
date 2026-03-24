<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    /**
     * Ultimos 2 digitos do ano atual + ID na tabela (ex.: 2026, id 15 → 2615).
     */
    public static function gerarMatricula(int $id): string
    {
        return sprintf('%02d%d', (int) now()->format('y'), $id);
    }

    protected $fillable = [
        'nome',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'data_nascimento',
        'sexo',
        'naturalidade',
        'naturalidade_estado',
        'nacionalidade',
        'rg',
        'orgao_exp',
        'rg_estado',
        'cpf',
        'estado_civil',
        'grau_escolaridade',
        'email',
        'empresa',
        'profissao',
        'telefone_profissional',
        'nome_pai',
        'nome_mae',
        'teacher_id',
        'servico_oferecido',
        'categoria_pretendida',
        'valor_pago',
        'observacao',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'valor_pago' => 'decimal:2',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
