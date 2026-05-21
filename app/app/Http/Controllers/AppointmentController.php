<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $viewData = $this->buildIndexViewData($request);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('appointments._agenda_content', $viewData)->render(),
            ]);
        }

        return view('appointments.index', $viewData);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'type' => ['required', Rule::in([Appointment::TYPE_LESSON, Appointment::TYPE_UNAVAILABLE])],
            'slot_date' => ['required', 'date'],
            'slot_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'vehicle_category' => ['nullable', 'string'],
            'schedule_mode' => ['nullable', Rule::in(['vehicle', 'teacher'])],
            'teacher' => ['nullable', 'integer', 'exists:teachers,id'],
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $startsAt = $this->resolveSlotDateTime($validated['slot_date'], $validated['slot_time']);
        $endsAt = $startsAt->copy()->addMinutes(50);
        $scheduleMode = ($validated['schedule_mode'] ?? 'vehicle') === 'teacher' ? 'teacher' : 'vehicle';
        $teacherContext = $scheduleMode === 'teacher'
            ? Teacher::query()->find($validated['teacher'] ?? $teacher->id)
            : null;
        $existingAppointment = Appointment::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('starts_at', $startsAt)
            ->first();
        $previousStudentId = $existingAppointment?->student_id;

        abort_if(! $teacher->isSchedulable(), 422, 'Este professor nao esta disponivel para aparecer na agenda de veiculos.');
        abort_if(! $teacher->supportsTimeSlot($validated['slot_time']), 422, 'Este horario esta fora do turno disponivel do professor.');
        abort_if(
            $existingAppointment && (int) $existingAppointment->teacher_id !== (int) $teacher->id,
            422,
            'Este veiculo ja possui um agendamento neste horario.'
        );

        $appointmentData = $validated['type'] === Appointment::TYPE_LESSON
            ? $this->resolveLessonData(
                $validated['student_id'] ?? null,
                $teacher,
                $vehicle,
                $startsAt,
                $existingAppointment
            )
            : ['student_id' => null, 'lesson_category' => null];

        $teacherConflict = Appointment::query()
            ->where('teacher_id', $teacher->id)
            ->where('starts_at', $startsAt)
            ->where('vehicle_id', '!=', $vehicle->id)
            ->exists();

        abort_if(
            $teacherConflict,
            422,
            'Este professor ja possui um agendamento neste horario.'
        );

        $appointment = Appointment::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'starts_at' => $startsAt,
            ],
            [
                'teacher_id' => $teacher->id,
                'student_id' => $appointmentData['student_id'],
                'type' => $validated['type'],
                'lesson_category' => $appointmentData['lesson_category'],
                'ends_at' => $endsAt,
                'notes' => $validated['notes'] ?? null,
            ]
        )->load(['student', 'teacher', 'vehicle']);

        $this->syncStudentLessonBalance($previousStudentId);
        $this->syncStudentLessonBalance($appointment->student_id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agenda atualizada com sucesso.',
                'slot_html' => $this->renderSlotHtml(
                    $appointment,
                    $vehicle,
                    $startsAt,
                    $validated['slot_time'],
                    (string) ($validated['vehicle_category'] ?? ''),
                    $scheduleMode,
                    $teacherContext
                ),
            ]);
        }

        return redirect()
            ->route('appointments.index', [
                'schedule_mode' => $scheduleMode,
                'teacher' => $teacherContext?->id,
                'vehicle' => $vehicle->id,
                'vehicle_category' => $validated['vehicle_category'] ?? null,
                'week_start' => Carbon::parse($validated['slot_date'])->startOfWeek(Carbon::MONDAY)->toDateString(),
            ])
            ->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $vehicleId = $appointment->vehicle_id;
        $vehicle = $appointment->vehicle()->firstOrFail();
        $slotDate = $appointment->starts_at->copy();
        $slotTime = $appointment->starts_at->format('H:i');
        $weekStart = $appointment->starts_at->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $vehicleCategory = $request->string('vehicle_category')->toString();
        $scheduleMode = $request->string('schedule_mode')->toString() === 'teacher' ? 'teacher' : 'vehicle';
        $teacherContext = $scheduleMode === 'teacher' && $request->filled('teacher')
            ? Teacher::query()->find((int) $request->integer('teacher'))
            : null;
        $studentId = $appointment->student_id;

        $appointment->delete();
        $this->syncStudentLessonBalance($studentId);
        $student = $studentId ? Student::query()->find($studentId) : null;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agendamento cancelado com sucesso.',
                'student_balance' => $student ? [
                    'a_contracted' => $student->quantidade_aulas_a_contratadas ?? 0,
                    'a_remaining' => $student->quantidade_aulas_a_restantes ?? ($student->quantidade_aulas_a_contratadas ?? 0),
                    'b_contracted' => $student->quantidade_aulas_b_contratadas ?? 0,
                    'b_remaining' => $student->quantidade_aulas_b_restantes ?? ($student->quantidade_aulas_b_contratadas ?? 0),
                ] : null,
                'slot_html' => $this->renderSlotHtml(
                    null,
                    $vehicle,
                    $slotDate,
                    $slotTime,
                    $vehicleCategory,
                    $scheduleMode,
                    $teacherContext
                ),
            ]);
        }

        if ($request->boolean('return_to_students')) {
            return redirect()
                ->route('students.index', $request->only(['tab', 'search', 'teacher_id', 'timeline_status']))
                ->with('success', 'Agendamento cancelado com sucesso.');
        }

        return redirect()
            ->route('appointments.index', [
                'schedule_mode' => $scheduleMode,
                'teacher' => $teacherContext?->id,
                'vehicle' => $vehicleId,
                'vehicle_category' => $vehicleCategory ?: null,
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

    private function resolveVehicle(Request $request, Collection $vehicles): ?Vehicle
    {
        if ($request->filled('vehicle')) {
            return $vehicles->firstWhere('id', (int) $request->integer('vehicle'));
        }

        return null;
    }

    private function resolveTeacher(Request $request, Collection $teachers): ?Teacher
    {
        if ($request->filled('teacher')) {
            return $teachers->firstWhere('id', (int) $request->integer('teacher'));
        }

        return null;
    }

    private function resolveSlotDateTime(string $date, string $time): Carbon
    {
        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time);

        abort_unless($startsAt && $this->timeSlots()->contains($time), 422);
        abort_unless($startsAt->dayOfWeekIso >= 1 && $startsAt->dayOfWeekIso <= 6, 422);

        return $startsAt;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndexViewData(Request $request): array
    {
        $requestedScheduleMode = $request->string('schedule_mode')->toString();
        $scheduleMode = in_array($requestedScheduleMode, ['vehicle', 'teacher'], true)
            ? $requestedScheduleMode
            : '';
        $categoryFilter = $request->string('vehicle_category')->toString();
        $allTeachers = Teacher::query()
            ->where('status_agendamento', Teacher::STATUS_AVAILABLE)
            ->orderBy('nome')
            ->get();
        $selectedTeacher = $scheduleMode === 'teacher'
            ? $this->resolveTeacher($request, $allTeachers)
            : null;

        $vehiclesQuery = Vehicle::query()
            ->when($categoryFilter !== '', function ($query) use ($categoryFilter) {
                $query->where('categoria', $categoryFilter);
            });

        if ($scheduleMode === 'teacher' && $selectedTeacher) {
            $teacherCategories = $selectedTeacher->schedulableCategories();
            $vehiclesQuery->whereIn('categoria', $teacherCategories ?: ['__none__']);
        }

        $vehicles = $vehiclesQuery->orderBy('placa')->get();

        $selectedVehicle = $this->resolveVehicle($request, $vehicles);
        $hasAgendaSelection = $scheduleMode === 'vehicle'
            ? $selectedVehicle !== null
            : $selectedTeacher !== null && $selectedVehicle !== null;
        $weekStart = $this->resolveWeekStart($request);
        $weekDays = collect(range(0, 5))
            ->map(fn (int $offset) => $weekStart->copy()->addDays($offset));

        $students = collect();
        $teachers = collect();
        $appointmentsBySlot = collect();
        $slotAppointmentsBySlot = collect();
        $busyTeacherIdsBySlot = collect();
        $busyStudentIdsBySlot = collect();
        if ($hasAgendaSelection && $selectedVehicle) {
            $teachers = $scheduleMode === 'teacher' && $selectedTeacher
                ? collect([$selectedTeacher])
                : $this->resolveTeachersForVehicle($selectedVehicle);
            $students = $this->resolveStudentsForVehicle($selectedVehicle);
            $appointmentsQuery = Appointment::query()
                ->with(['student', 'teacher', 'vehicle'])
                ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()]);

            if ($scheduleMode === 'teacher' && $selectedTeacher) {
                $appointmentsQuery->where(function ($query) use ($selectedTeacher, $selectedVehicle) {
                    $query
                        ->where('teacher_id', $selectedTeacher->id)
                        ->orWhere('vehicle_id', $selectedVehicle->id);
                });
            } else {
                $appointmentsQuery->where('vehicle_id', $selectedVehicle->id);
            }

            $slotAppointmentsBySlot = $appointmentsQuery
                ->get()
                ->groupBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'));
            $appointmentsBySlot = $slotAppointmentsBySlot
                ->map(fn (Collection $appointments) => $appointments->first());
            $busyTeacherIdsBySlot = $this->buildBusyParticipantMap('teacher_id', $teachers->pluck('id'), $weekStart);
            $busyStudentIdsBySlot = $this->buildBusyParticipantMap('student_id', $students->pluck('id'), $weekStart);
        }

        return [
            'scheduleMode' => $scheduleMode,
            'hasAgendaSelection' => $hasAgendaSelection,
            'allTeachers' => $allTeachers,
            'selectedTeacher' => $selectedTeacher,
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'teachers' => $teachers,
            'students' => $students,
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'timeSlots' => $this->timeSlots(),
            'appointmentsBySlot' => $appointmentsBySlot,
            'slotAppointmentsBySlot' => $slotAppointmentsBySlot,
            'busyTeacherIdsBySlot' => $busyTeacherIdsBySlot,
            'busyStudentIdsBySlot' => $busyStudentIdsBySlot,
            'vehicleCategoryFilter' => $categoryFilter,
            'vehicleCategoryOptions' => Vehicle::categoryOptions(),
            'weekDayLabels' => [
                1 => 'Segunda-feira',
                2 => 'Terca-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sabado',
            ],
            'studentCategoryLabels' => Student::lessonCategoryLabels(),
        ];
    }

    /**
     * @return Collection<int, Teacher>
     */
    private function resolveTeachersForVehicle(Vehicle $vehicle): Collection
    {
        return Teacher::query()
            ->whereJsonContains('categorias_ensino', $vehicle->categoria)
            ->where('status_agendamento', Teacher::STATUS_AVAILABLE)
            ->orderBy('nome')
            ->get();
    }

    /**
     * @return Collection<int, Student>
     */
    private function resolveStudentsForVehicle(Vehicle $vehicle): Collection
    {
        return Student::query()
            ->where('status', '!=', Student::STATUS_FINISHED)
            ->with('teacher')
            ->orderBy('nome')
            ->get()
            ->each->syncRemainingLessons()
            ->filter(fn (Student $student) => $student->supportsLessonCategory($vehicle->categoria))
            ->filter(fn (Student $student) => $student->hasRemainingLessonsForCategory($vehicle->categoria))
            ->values();
    }

    private function renderSlotHtml(
        ?Appointment $appointment,
        Vehicle $vehicle,
        Carbon $slotDate,
        string $slotTime,
        string $vehicleCategoryFilter,
        string $scheduleMode = 'vehicle',
        ?Teacher $selectedTeacher = null
    ): string {
        $teachers = $scheduleMode === 'teacher' && $selectedTeacher
            ? collect([$selectedTeacher])
            : $this->resolveTeachersForVehicle($vehicle);
        $students = $this->resolveStudentsForVehicle($vehicle);
        $slotKey = $slotDate->copy()->format('Y-m-d').' '.$slotTime;
        $slotStart = $this->resolveSlotDateTime($slotDate->toDateString(), $slotTime);
        $slotAppointmentsQuery = Appointment::query()
            ->with(['student', 'teacher', 'vehicle'])
            ->where('starts_at', $slotStart);

        if ($scheduleMode === 'teacher' && $selectedTeacher) {
            $slotAppointmentsQuery->where(function ($query) use ($selectedTeacher, $vehicle) {
                $query
                    ->where('teacher_id', $selectedTeacher->id)
                    ->orWhere('vehicle_id', $vehicle->id);
            });
        } else {
            $slotAppointmentsQuery->where('vehicle_id', $vehicle->id);
        }

        $slotAppointments = $slotAppointmentsQuery->get();
        $appointment = $slotAppointments->firstWhere('vehicle_id', $vehicle->id) ?? $appointment;

        return view('appointments._slot_cell', [
            'appointment' => $appointment,
            'day' => $slotDate->copy(),
            'slot' => $slotTime,
            'selectedVehicle' => $vehicle,
            'teachers' => $teachers,
            'students' => $students,
            'busyTeacherIdsBySlot' => collect([
                $slotKey => $this->buildBusyParticipantMap('teacher_id', $teachers->pluck('id'), $slotDate->copy()->startOfWeek(Carbon::MONDAY))->get($slotKey, []),
            ]),
            'busyStudentIdsBySlot' => collect([
                $slotKey => $this->buildBusyParticipantMap('student_id', $students->pluck('id'), $slotDate->copy()->startOfWeek(Carbon::MONDAY))->get($slotKey, []),
            ]),
            'vehicleCategoryFilter' => $vehicleCategoryFilter,
            'studentCategoryLabels' => Student::lessonCategoryLabels(),
            'scheduleMode' => $scheduleMode,
            'selectedTeacher' => $selectedTeacher,
            'slotAppointments' => $slotAppointments,
        ])->render();
    }

    /**
     * @param Collection<int, int> $participantIds
     * @return Collection<string, array<int, int>>
     */
    private function buildBusyParticipantMap(string $column, Collection $participantIds, Carbon $weekStart): Collection
    {
        if ($participantIds->isEmpty()) {
            return collect();
        }

        return Appointment::query()
            ->whereNotNull($column)
            ->whereIn($column, $participantIds->all())
            ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
            ->get([$column, 'starts_at'])
            ->groupBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'))
            ->map(fn (Collection $appointments) => $appointments
                ->pluck($column)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all()
            );
    }

    /**
     * @return array{student_id:int, lesson_category:string}
     */
    private function resolveLessonData(
        ?int $studentId,
        Teacher $teacher,
        Vehicle $vehicle,
        Carbon $startsAt,
        ?Appointment $currentAppointment = null
    ): array
    {
        abort_if(! $studentId, 422, 'Selecione um aluno para marcar a aula.');

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
            ! $teacher->teachesCategory($vehicle->categoria),
            422,
            'O professor selecionado nao ensina a categoria deste veiculo.'
        );

        abort_if(
            ! $student->supportsLessonCategory($vehicle->categoria),
            422,
            'A categoria do veiculo nao e compativel com o cadastro do aluno.'
        );

        $student->loadMissing('appointments');
        $student->syncRemainingLessons();

        $remainingLessons = $student->remainingLessonsForCategory($vehicle->categoria);
        $isSameReservedLesson = $currentAppointment
            && (int) $currentAppointment->student_id === (int) $student->id
            && $currentAppointment->type === Appointment::TYPE_LESSON
            && $currentAppointment->lesson_category === $vehicle->categoria
            && $currentAppointment->countsAsConsumedLesson();

        abort_if(
            ($remainingLessons ?? 0) <= 0 && ! $isSameReservedLesson,
            422,
            'Este aluno nao possui mais aulas restantes para a categoria deste veiculo.'
        );

        $studentConflict = Appointment::query()
            ->where('student_id', $student->id)
            ->where('starts_at', $startsAt)
            ->when($currentAppointment, fn ($query) => $query->where('id', '!=', $currentAppointment->id))
            ->exists();

        abort_if(
            $studentConflict,
            422,
            'Este aluno ja possui uma aula agendada neste horario.'
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
            'lesson_category' => $vehicle->categoria,
        ];
    }

    private function syncStudentLessonBalance(?int $studentId): void
    {
        if (! $studentId) {
            return;
        }

        $student = Student::query()
            ->with('appointments')
            ->find($studentId);

        if ($student) {
            $student->syncRemainingLessons();
        }
    }
}
