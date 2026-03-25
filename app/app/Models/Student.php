<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    public const STATUS_THEORY_CLASS = 'em_aula_teorica';
    public const STATUS_THEORY_PASSED = 'passou_na_prova_teorica';
    public const STATUS_PRACTICAL_CLASS = 'em_aula_pratica';
    public const STATUS_FINISHED = 'finalizado';

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
        'status',
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

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_THEORY_CLASS => 'Em aula teorica',
            self::STATUS_THEORY_PASSED => 'Passou na prova teorica',
            self::STATUS_PRACTICAL_CLASS => 'Em aula pratica',
            self::STATUS_FINISHED => 'Finalizado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    /**
     * @return list<string>
     */
    public static function statusFlow(): array
    {
        return [
            self::STATUS_THEORY_CLASS,
            self::STATUS_THEORY_PASSED,
            self::STATUS_PRACTICAL_CLASS,
            self::STATUS_FINISHED,
        ];
    }

    public function nextStatus(): ?string
    {
        $flow = self::statusFlow();
        $index = array_search($this->status, $flow, true);

        if ($index === false || ! isset($flow[$index + 1])) {
            return null;
        }

        return $flow[$index + 1];
    }

    /**
     * @return array<string, string>
     */
    public static function lessonCategoryLabels(): array
    {
        return [
            'A' => 'Aula A',
            'B' => 'Aula B',
            'AB' => 'Aula A ou B',
        ];
    }

    public function lessonCategoryLabel(): string
    {
        return self::lessonCategoryLabels()[$this->categoria_pretendida] ?? 'Categoria nao informada';
    }

    public function supportsLessonCategory(string $lessonCategory): bool
    {
        if ($this->categoria_pretendida === null || $this->categoria_pretendida === '') {
            return in_array($lessonCategory, ['A', 'B'], true);
        }

        if ($this->categoria_pretendida === 'AB') {
            return in_array($lessonCategory, ['A', 'B'], true);
        }

        return $this->categoria_pretendida === $lessonCategory;
    }
}
