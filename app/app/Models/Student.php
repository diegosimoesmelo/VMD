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
        'quantidade_aulas_a_contratadas',
        'quantidade_aulas_b_contratadas',
        'observacao',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'valor_pago' => 'decimal:2',
        'quantidade_aulas_contratadas' => 'integer',
        'quantidade_aulas_restantes' => 'integer',
        'quantidade_aulas_a_contratadas' => 'integer',
        'quantidade_aulas_a_restantes' => 'integer',
        'quantidade_aulas_b_contratadas' => 'integer',
        'quantidade_aulas_b_restantes' => 'integer',
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

    public function consumedLessonsCount(?string $lessonCategory = null): int
    {
        $appointments = $this->relationLoaded('appointments')
            ? $this->appointments
            : $this->appointments()->get();

        return $appointments
            ->filter(function (Appointment $appointment) use ($lessonCategory) {
                if (! $appointment->countsAsConsumedLesson()) {
                    return false;
                }

                if ($lessonCategory === null) {
                    return true;
                }

                return $appointment->lesson_category === $lessonCategory;
            })
            ->count();
    }

    public function contractedLessonsForCategory(string $lessonCategory): ?int
    {
        return match ($lessonCategory) {
            'A' => $this->quantidade_aulas_a_contratadas,
            'B' => $this->quantidade_aulas_b_contratadas,
            default => null,
        };
    }

    public function remainingLessonsForCategory(string $lessonCategory): ?int
    {
        return match ($lessonCategory) {
            'A' => $this->quantidade_aulas_a_restantes,
            'B' => $this->quantidade_aulas_b_restantes,
            default => null,
        };
    }

    public function calculateRemainingLessonsForCategory(string $lessonCategory): ?int
    {
        $contractedLessons = $this->contractedLessonsForCategory($lessonCategory);

        if ($contractedLessons === null) {
            return null;
        }

        return max($contractedLessons - $this->consumedLessonsCount($lessonCategory), 0);
    }

    public function syncRemainingLessons(): void
    {
        $remainingLessonsA = $this->calculateRemainingLessonsForCategory('A');
        $remainingLessonsB = $this->calculateRemainingLessonsForCategory('B');
        $totalContractedLessons = collect([
            $this->quantidade_aulas_a_contratadas,
            $this->quantidade_aulas_b_contratadas,
        ])->filter(fn ($value) => $value !== null)->sum();
        $hasAnyContractedLessons = $this->quantidade_aulas_a_contratadas !== null || $this->quantidade_aulas_b_contratadas !== null;
        $totalRemainingLessons = ($remainingLessonsA ?? 0) + ($remainingLessonsB ?? 0);

        if (
            $this->quantidade_aulas_a_restantes === $remainingLessonsA
            && $this->quantidade_aulas_b_restantes === $remainingLessonsB
            && $this->quantidade_aulas_contratadas === ($hasAnyContractedLessons ? $totalContractedLessons : null)
            && $this->quantidade_aulas_restantes === ($hasAnyContractedLessons ? $totalRemainingLessons : null)
        ) {
            return;
        }

        $this->forceFill([
            'quantidade_aulas_a_restantes' => $remainingLessonsA,
            'quantidade_aulas_b_restantes' => $remainingLessonsB,
            'quantidade_aulas_contratadas' => $hasAnyContractedLessons ? $totalContractedLessons : null,
            'quantidade_aulas_restantes' => $hasAnyContractedLessons ? $totalRemainingLessons : null,
        ])->saveQuietly();
    }

    public function hasRemainingLessonsForCategory(string $lessonCategory): bool
    {
        $remainingLessons = $this->remainingLessonsForCategory($lessonCategory);

        if ($remainingLessons === null) {
            return false;
        }

        return $remainingLessons > 0;
    }
}
