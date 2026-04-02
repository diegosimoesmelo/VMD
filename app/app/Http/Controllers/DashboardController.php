<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user?->hasRole(User::ROLE_TEACHER)) {
            return $this->teacherDashboard($user);
        }

        return $this->administrativeDashboard($request);
    }

    private function administrativeDashboard(Request $request): View
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse((string) $request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $teacherFilter = $request->integer('teacher_id');
        $vehicleFilter = $request->integer('vehicle_id');
        $weekDays = collect(range(0, 5))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $timeSlots = $this->timeSlots();

        $teachers = Teacher::query()
            ->when($teacherFilter > 0, fn ($query) => $query->whereKey($teacherFilter))
            ->with([
                'appointments' => fn ($query) => $query
                    ->with(['student', 'vehicle'])
                    ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
                    ->orderBy('starts_at'),
            ])
            ->orderBy('nome')
            ->get();

        $vehicles = Vehicle::query()
            ->when($vehicleFilter > 0, fn ($query) => $query->whereKey($vehicleFilter))
            ->with([
                'appointments' => fn ($query) => $query
                    ->with(['student', 'teacher'])
                    ->whereBetween('starts_at', [$weekStart->copy()->startOfDay(), $weekStart->copy()->addDays(5)->endOfDay()])
                    ->orderBy('starts_at'),
            ])
            ->orderBy('placa')
            ->get();

        return view('dashboard', [
            'mode' => 'administrative',
            'stats' => [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'vehicles' => Vehicle::query()->count(),
                'appointments_today' => Appointment::query()
                    ->whereDate('starts_at', today())
                    ->count(),
            ],
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'timeSlots' => $timeSlots,
            'allTeachers' => Teacher::query()->orderBy('nome')->get(['id', 'nome']),
            'allVehicles' => Vehicle::query()->orderBy('placa')->get(['id', 'placa']),
            'teacherFilter' => $teacherFilter > 0 ? $teacherFilter : null,
            'vehicleFilter' => $vehicleFilter > 0 ? $vehicleFilter : null,
            'teachers' => $teachers,
            'vehicles' => $vehicles,
            'teacherSchedules' => $teachers->mapWithKeys(fn (Teacher $teacher) => [
                $teacher->id => $this->buildTeacherScheduleGrid($teacher, $weekDays, $timeSlots),
            ]),
            'vehicleSchedules' => $vehicles->mapWithKeys(fn (Vehicle $vehicle) => [
                $vehicle->id => $this->buildVehicleScheduleGrid($vehicle, $weekDays, $timeSlots),
            ]),
        ]);
    }

    private function teacherDashboard(User $user): View
    {
        $teacher = $user->teacher;
        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(5)->endOfDay();

        $appointments = collect();
        $appointmentsByDay = collect();
        if ($teacher) {
            $appointments = $teacher->appointments()
                ->with(['student', 'vehicle'])
                ->whereBetween('starts_at', [$now->copy(), $weekEnd])
                ->orderBy('starts_at')
                ->get();

            $appointmentsByDay = $appointments->groupBy(fn (Appointment $appointment) => $appointment->starts_at->toDateString());
        }

        $weekDays = collect(range(0, 5))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $upcomingAppointment = $appointments->first();

        return view('dashboard', [
            'mode' => 'teacher',
            'teacher' => $teacher,
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'appointmentsByDay' => $appointmentsByDay,
            'summary' => [
                'total_week' => $appointments->count(),
                'today' => $appointments->filter(fn (Appointment $appointment) => $appointment->starts_at->isToday())->count(),
                'next' => $upcomingAppointment,
            ],
        ]);
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

    /**
     * @return Collection<string, Collection<string, ?Appointment>>
     */
    private function buildTeacherScheduleGrid(Teacher $teacher, Collection $weekDays, Collection $timeSlots): Collection
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

    /**
     * @return Collection<string, Collection<string, ?Appointment>>
     */
    private function buildVehicleScheduleGrid(Vehicle $vehicle, Collection $weekDays, Collection $timeSlots): Collection
    {
        $appointmentsBySlot = $vehicle->appointments
            ->keyBy(fn (Appointment $appointment) => $appointment->starts_at->format('Y-m-d H:i'));

        return $timeSlots->mapWithKeys(function (string $slot) use ($appointmentsBySlot, $weekDays) {
            $days = $weekDays->mapWithKeys(function (Carbon $day) use ($appointmentsBySlot, $slot) {
                $slotKey = $day->format('Y-m-d').' '.$slot;

                return [$day->toDateString() => $appointmentsBySlot->get($slotKey)];
            });

            return [$slot => $days];
        });
    }
}
