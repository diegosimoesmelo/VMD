<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    private const DEFAULT_PASSWORD = 'vmdcfc';

    public function index(Request $request): View
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse((string) $request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekDays = collect(range(0, 5))
            ->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $timeSlots = collect([
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

        $teachers = Teacher::query()
            ->with([
                'appointments' => fn ($query) => $query
                    ->with(['student', 'vehicle'])
                    ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
                    ->orderBy('starts_at'),
            ])
            ->orderBy('nome')
            ->get();

        $teacherSchedules = $teachers
            ->mapWithKeys(fn (Teacher $teacher) => [
                $teacher->id => $this->buildScheduleGrid($teacher, $weekDays, $timeSlots),
            ]);

        return view('teachers.index', compact('teachers', 'weekStart', 'weekDays', 'timeSlots', 'teacherSchedules'));
    }

    public function create(): View
    {
        return view('teachers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['status_agendamento'] = $validated['status_agendamento'] ?? Teacher::STATUS_AVAILABLE;

        $teacher = Teacher::create($validated);
        $this->syncTeacherUser($teacher, true);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Professor cadastrado com sucesso. Usuario do professor criado com senha inicial: '.self::DEFAULT_PASSWORD);
    }

    public function edit(Teacher $teacher): View
    {
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate($this->rules($teacher));
        $validated['status_agendamento'] = $validated['status_agendamento'] ?? Teacher::STATUS_AVAILABLE;

        $teacher->update($validated);
        $this->syncTeacherUser($teacher);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Dados do professor atualizados com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Teacher $teacher = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teachers', 'cpf')->ignore($teacher?->id),
            ],
            'telefone' => ['required', 'string', 'max:30'],
            'categorias_ensino' => ['required', 'array', 'min:1'],
            'categorias_ensino.*' => ['required', 'string', Rule::in(Teacher::categoryOptions())],
            'turnos_disponiveis' => ['required', 'array', 'min:1'],
            'turnos_disponiveis.*' => ['required', 'string', Rule::in(array_keys(Teacher::shiftOptions()))],
            'status_agendamento' => ['nullable', 'string', Rule::in(array_keys(Teacher::schedulingStatusOptions()))],
        ];
    }

    /**
     * @return Collection<string, Collection<string, ?Appointment>>
     */
    private function buildScheduleGrid(Teacher $teacher, Collection $weekDays, Collection $timeSlots): Collection
    {
        $appointmentsBySlot = $teacher->appointments
            ->keyBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'));

        return $timeSlots->mapWithKeys(function (string $slot) use ($appointmentsBySlot, $weekDays) {
            $days = $weekDays->mapWithKeys(function (Carbon $day) use ($appointmentsBySlot, $slot) {
                $slotKey = $day->format('Y-m-d').' '.$slot;

                return [$day->toDateString() => $appointmentsBySlot->get($slotKey)];
            });

            return [$slot => $days];
        });
    }

    private function syncTeacherUser(Teacher $teacher, bool $forcePasswordReset = false): void
    {
        $username = preg_replace('/\D+/', '', (string) $teacher->cpf) ?: 'professor'.$teacher->id;
        $user = $teacher->user;

        if (! $user) {
            User::create([
                'teacher_id' => $teacher->id,
                'name' => $teacher->nome,
                'username' => $username,
                'role' => User::ROLE_TEACHER,
                'password' => self::DEFAULT_PASSWORD,
                'must_change_password' => true,
            ]);

            return;
        }

        $payload = [
            'name' => $teacher->nome,
            'username' => $username,
            'role' => User::ROLE_TEACHER,
        ];

        if ($forcePasswordReset) {
            $payload['password'] = self::DEFAULT_PASSWORD;
            $payload['must_change_password'] = true;
        }

        $user->update($payload);
    }
}
