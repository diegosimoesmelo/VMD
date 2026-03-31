<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user?->hasRole(User::ROLE_TEACHER)) {
            return $this->teacherDashboard($user);
        }

        return $this->administrativeDashboard();
    }

    private function administrativeDashboard(): View
    {
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
}
