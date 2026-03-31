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
        ]);

        $vehicle = Vehicle::query()->findOrFail($validated['vehicle_id']);
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $startsAt = $this->resolveSlotDateTime($validated['slot_date'], $validated['slot_time']);
        $endsAt = $startsAt->copy()->addMinutes(50);

        abort_if(! $teacher->isSchedulable(), 422, 'Este professor nao esta disponivel para aparecer na agenda de veiculos.');
        abort_if(! $teacher->supportsTimeSlot($validated['slot_time']), 422, 'Este horario esta fora do turno disponivel do professor.');

        $appointmentData = $validated['type'] === Appointment::TYPE_LESSON
            ? $this->resolveLessonData(
                $validated['student_id'] ?? null,
                $teacher,
                $vehicle,
                $startsAt
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Agenda atualizada com sucesso.',
                'slot_html' => $this->renderSlotHtml(
                    $appointment,
                    $vehicle,
                    $startsAt,
                    $validated['slot_time'],
                    $this->resolveTeachersForVehicle($vehicle),
                    $this->resolveStudentsForVehicle($vehicle),
                    (string) ($validated['vehicle_category'] ?? '')
                ),
            ]);
        }

        return redirect()
            ->route('appointments.index', [
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

        $appointment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Horario liberado com sucesso.',
                'slot_html' => $this->renderSlotHtml(
                    null,
                    $vehicle,
                    $slotDate,
                    $slotTime,
                    $this->resolveTeachersForVehicle($vehicle),
                    $this->resolveStudentsForVehicle($vehicle),
                    $vehicleCategory
                ),
            ]);
        }

        return redirect()
            ->route('appointments.index', [
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

        return $vehicles->first();
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
        $categoryFilter = $request->string('vehicle_category')->toString();
        $vehicles = Vehicle::query()
            ->when($categoryFilter !== '', function ($query) use ($categoryFilter) {
                $query->where('categoria', $categoryFilter);
            })
            ->orderBy('placa')
            ->get();

        $selectedVehicle = $this->resolveVehicle($request, $vehicles);
        $weekStart = $this->resolveWeekStart($request);
        $weekDays = collect(range(0, 5))
            ->map(fn (int $offset) => $weekStart->copy()->addDays($offset));

        $students = collect();
        $teachers = collect();
        $appointmentsBySlot = collect();
        if ($selectedVehicle) {
            $teachers = $this->resolveTeachersForVehicle($selectedVehicle);
            $students = $this->resolveStudentsForVehicle($selectedVehicle);
            $appointmentsBySlot = Appointment::query()
                ->with(['student', 'teacher', 'vehicle'])
                ->where('vehicle_id', $selectedVehicle->id)
                ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
                ->get()
                ->keyBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'));
        }

        return [
            'vehicles' => $vehicles,
            'selectedVehicle' => $selectedVehicle,
            'teachers' => $teachers,
            'students' => $students,
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'timeSlots' => $this->timeSlots(),
            'appointmentsBySlot' => $appointmentsBySlot,
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
            ->filter(fn (Student $student) => $student->supportsLessonCategory($vehicle->categoria))
            ->values();
    }

    private function renderSlotHtml(
        ?Appointment $appointment,
        Vehicle $vehicle,
        Carbon $slotDate,
        string $slotTime,
        Collection $teachers,
        Collection $students,
        string $vehicleCategoryFilter
    ): string {
        return view('appointments._slot_cell', [
            'appointment' => $appointment,
            'day' => $slotDate->copy(),
            'slot' => $slotTime,
            'selectedVehicle' => $vehicle,
            'teachers' => $teachers,
            'students' => $students,
            'vehicleCategoryFilter' => $vehicleCategoryFilter,
            'studentCategoryLabels' => Student::lessonCategoryLabels(),
        ])->render();
    }

    /**
     * @return array{student_id:int, lesson_category:string}
     */
    private function resolveLessonData(?int $studentId, Teacher $teacher, Vehicle $vehicle, Carbon $startsAt): array
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
}
