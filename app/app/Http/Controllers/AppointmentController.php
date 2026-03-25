<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $categoryFilter = $request->string('teacher_category')->toString();
        $teachers = Teacher::query()
            ->with('vehicles')
            ->when($categoryFilter !== '', function ($query) use ($categoryFilter) {
                $query->whereJsonContains('categorias_ensino', $categoryFilter);
            })
            ->orderBy('nome')
            ->get();

        $selectedTeacher = $this->resolveTeacher($request, $teachers);
        $weekStart = $this->resolveWeekStart($request);
        $weekDays = collect(range(0, 5))
            ->map(fn (int $offset) => $weekStart->copy()->addDays($offset));

        $students = collect();
        $appointmentsBySlot = collect();

        if ($selectedTeacher) {
            $students = Student::query()
                ->where('status', '!=', Student::STATUS_FINISHED)
                ->with('teacher')
                ->orderByRaw('CASE WHEN teacher_id = ? THEN 0 WHEN teacher_id IS NULL THEN 1 ELSE 2 END', [$selectedTeacher->id])
                ->orderBy('nome')
                ->get();

            $appointmentsBySlot = Appointment::query()
                ->with(['student', 'teacher', 'vehicle'])
                ->where('teacher_id', $selectedTeacher->id)
                ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
                ->get()
                ->keyBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'));
        }

        return view('appointments.index', [
            'teachers' => $teachers,
            'selectedTeacher' => $selectedTeacher,
            'students' => $students,
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'timeSlots' => $this->timeSlots(),
            'appointmentsBySlot' => $appointmentsBySlot,
            'teacherCategoryFilter' => $categoryFilter,
            'teacherCategoryOptions' => Teacher::categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'type' => ['required', Rule::in([Appointment::TYPE_LESSON, Appointment::TYPE_UNAVAILABLE])],
            'lesson_category' => ['nullable', 'string', Rule::in(['A', 'B'])],
            'slot_date' => ['required', 'date'],
            'slot_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'teacher_category' => ['nullable', 'string'],
        ]);

        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $startsAt = $this->resolveSlotDateTime($validated['slot_date'], $validated['slot_time']);
        $endsAt = $startsAt->copy()->addMinutes(50);
        abort_if(! $teacher->supportsTimeSlot($validated['slot_time']), 422, 'Este horario esta fora do turno disponivel do professor.');

        $lessonData = $validated['type'] === Appointment::TYPE_LESSON
            ? $this->resolveLessonData(
                $validated['student_id'] ?? null,
                $validated['vehicle_id'] ?? null,
                $validated['lesson_category'] ?? null,
                $teacher,
                $startsAt
            )
            : ['student_id' => null, 'vehicle_id' => null, 'lesson_category' => null];

        Appointment::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'starts_at' => $startsAt,
            ],
            [
                'student_id' => $lessonData['student_id'],
                'vehicle_id' => $lessonData['vehicle_id'],
                'type' => $validated['type'],
                'lesson_category' => $lessonData['lesson_category'],
                'ends_at' => $endsAt,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('appointments.index', [
                'teacher' => $teacher->id,
                'teacher_category' => $validated['teacher_category'] ?? null,
                'week_start' => Carbon::parse($validated['slot_date'])->startOfWeek(Carbon::MONDAY)->toDateString(),
            ])
            ->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $teacherId = $appointment->teacher_id;
        $weekStart = $appointment->starts_at->copy()->startOfWeek(Carbon::MONDAY)->toDateString();

        $appointment->delete();

        return redirect()
            ->route('appointments.index', [
                'teacher' => $teacherId,
                'teacher_category' => $request->string('teacher_category')->toString() ?: null,
                'week_start' => $weekStart,
            ])
            ->with('success', 'Horario liberado com sucesso.');
    }

    /**
     * @return Collection<int, string>
     */
    private function timeSlots(): Collection
    {
        return collect([
            '07:00',
            '07:50',
            '08:40',
            '09:30',
            '10:20',
            '11:10',
            '14:00',
            '14:50',
            '15:40',
            '16:30',
            '17:20',
        ]);
    }

    private function resolveWeekStart(Request $request): Carbon
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse((string) $request->input('week_start'))
            : now();

        return $weekStart->startOfWeek(Carbon::MONDAY);
    }

    private function resolveTeacher(Request $request, Collection $teachers): ?Teacher
    {
        if ($request->filled('teacher')) {
            return $teachers->firstWhere('id', (int) $request->integer('teacher'));
        }

        return $teachers->first();
    }

    private function resolveSlotDateTime(string $date, string $time): Carbon
    {
        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time);

        abort_unless($startsAt && $this->timeSlots()->contains($time), 422);
        abort_unless($startsAt->dayOfWeekIso >= 1 && $startsAt->dayOfWeekIso <= 6, 422);

        return $startsAt;
    }

    /**
     * @return array{student_id:int, vehicle_id:int, lesson_category:string}
     */
    private function resolveLessonData(?int $studentId, ?int $vehicleId, ?string $lessonCategory, Teacher $teacher, Carbon $startsAt): array
    {
        abort_if(! $studentId, 422, 'Selecione um aluno para marcar a aula.');
        abort_if(! $vehicleId, 422, 'Selecione um veiculo para marcar a aula.');
        abort_if(! $lessonCategory, 422, 'Informe se a aula sera de categoria A ou B.');

        $student = Student::query()
            ->whereKey($studentId)
            ->first();

        abort_unless($student, 422, 'Aluno nao encontrado.');

        abort_if(
            $student->status === Student::STATUS_FINISHED,
            422,
            'Nao e possivel agendar novas aulas para um aluno finalizado.'
        );

        abort_if(
            ! $teacher->teachesCategory($lessonCategory),
            422,
            'O professor selecionado nao ensina esta categoria.'
        );

        abort_if(
            ! $this->studentSupportsLessonCategory($student, $lessonCategory),
            422,
            'A categoria da aula nao e compativel com o cadastro do aluno.'
        );

        $vehicle = Vehicle::query()
            ->whereKey($vehicleId)
            ->first();

        abort_unless($vehicle, 422, 'Veiculo nao encontrado.');

        abort_if(
            (int) $vehicle->teacher_id !== (int) $teacher->id,
            422,
            'O veiculo selecionado nao pertence ao professor escolhido.'
        );

        abort_if(
            $vehicle->categoria !== $lessonCategory,
            422,
            'A categoria do veiculo precisa ser igual a categoria da aula.'
        );

        $vehicleConflict = Appointment::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('starts_at', $startsAt)
            ->where('teacher_id', '!=', $teacher->id)
            ->exists();

        abort_if(
            $vehicleConflict,
            422,
            'Este veiculo ja possui um agendamento neste horario.'
        );

        if (! $student->teacher_id) {
            $student->update(['teacher_id' => $teacher->id]);
        }

        return [
            'student_id' => (int) $student->id,
            'vehicle_id' => (int) $vehicle->id,
            'lesson_category' => $lessonCategory,
        ];
    }

    private function studentSupportsLessonCategory(Student $student, string $lessonCategory): bool
    {
        if ($student->categoria_pretendida === null || $student->categoria_pretendida === '') {
            return in_array($lessonCategory, ['A', 'B'], true);
        }

        if ($student->categoria_pretendida === 'AB') {
            return in_array($lessonCategory, ['A', 'B'], true);
        }

        return $student->categoria_pretendida === $lessonCategory;
    }
}
