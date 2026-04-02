<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    public const TYPE_LESSON = 'lesson';
    public const TYPE_UNAVAILABLE = 'unavailable';

    public const LESSON_STATUS_SCHEDULED = 'scheduled';
    public const LESSON_STATUS_COMPLETED = 'completed';
    public const LESSON_STATUS_STUDENT_ABSENT = 'student_absent';
    public const LESSON_STATUS_VEHICLE_ISSUE = 'vehicle_issue';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'vehicle_id',
        'type',
        'lesson_category',
        'starts_at',
        'ends_at',
        'notes',
        'lesson_status',
        'lesson_status_notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return array<string, string>
     */
    public static function lessonStatusOptions(): array
    {
        return [
            self::LESSON_STATUS_COMPLETED => 'Aula concluida',
            self::LESSON_STATUS_STUDENT_ABSENT => 'Aluno nao compareceu',
            self::LESSON_STATUS_VEHICLE_ISSUE => 'Problema com o carro',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function effectiveLessonStatusLabels(): array
    {
        return [
            self::LESSON_STATUS_SCHEDULED => 'Agendada',
            self::LESSON_STATUS_COMPLETED => 'Aula concluida',
            self::LESSON_STATUS_STUDENT_ABSENT => 'Aluno nao compareceu',
            self::LESSON_STATUS_VEHICLE_ISSUE => 'Problema com o carro',
        ];
    }

    public function effectiveLessonStatus(?CarbonInterface $reference = null): string
    {
        if ($this->type !== self::TYPE_LESSON) {
            return self::LESSON_STATUS_SCHEDULED;
        }

        if ($this->lesson_status !== null && $this->lesson_status !== '') {
            return $this->lesson_status;
        }

        $reference ??= now();

        if ($this->ends_at && $reference->greaterThanOrEqualTo($this->ends_at)) {
            return self::LESSON_STATUS_COMPLETED;
        }

        return self::LESSON_STATUS_SCHEDULED;
    }

    public function effectiveLessonStatusLabel(?CarbonInterface $reference = null): string
    {
        $status = $this->effectiveLessonStatus($reference);

        return self::effectiveLessonStatusLabels()[$status] ?? $status;
    }

    public function countsAsConsumedLesson(?CarbonInterface $reference = null): bool
    {
        if ($this->type !== self::TYPE_LESSON) {
            return false;
        }

        return in_array(
            $this->effectiveLessonStatus($reference),
            [
                self::LESSON_STATUS_SCHEDULED,
                self::LESSON_STATUS_COMPLETED,
                self::LESSON_STATUS_STUDENT_ABSENT,
            ],
            true
        );
    }
}
