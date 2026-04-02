<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonMonitoringController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $viewData = $this->buildIndexViewData($request);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('lesson-monitoring._content', $viewData)->render(),
            ]);
        }

        return view('lesson-monitoring.index', $viewData);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        abort_if($appointment->type !== Appointment::TYPE_LESSON, 422, 'Somente aulas podem receber apontamentos operacionais.');

        $validated = $request->validate([
            'lesson_status' => ['nullable', 'string', Rule::in(array_keys(Appointment::lessonStatusOptions()))],
            'lesson_status_notes' => ['nullable', 'string'],
        ]);

        $appointment->update([
            'lesson_status' => $validated['lesson_status'] ?: null,
            'lesson_status_notes' => ($validated['lesson_status'] ?? null) ? ($validated['lesson_status_notes'] ?? null) : null,
        ]);

        $appointment->load(['student', 'teacher', 'vehicle']);
        $appointment->student?->loadMissing('appointments');
        $appointment->student?->syncRemainingLessons();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Apontamento da aula atualizado com sucesso.',
                'slot_html' => $this->renderSlotHtml($appointment),
            ]);
        }

        return redirect()
            ->route('lesson-monitoring.index', [
                'vehicle' => $appointment->vehicle_id,
                'vehicle_category' => $request->string('vehicle_category')->toString() ?: null,
                'week_start' => $appointment->starts_at->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
            ])
            ->with('success', 'Apontamento da aula atualizado com sucesso.');
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

        $selectedVehicle = $request->filled('vehicle')
            ? $vehicles->firstWhere('id', (int) $request->integer('vehicle'))
            : $vehicles->first();

        $weekStart = $request->filled('week_start')
            ? Carbon::parse((string) $request->input('week_start'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekDays = collect(range(0, 5))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));

        $appointmentsBySlot = collect();
        if ($selectedVehicle) {
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
            'lessonStatusOptions' => Appointment::lessonStatusOptions(),
        ];
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

    private function renderSlotHtml(Appointment $appointment): string
    {
        return view('lesson-monitoring._slot_cell', [
            'appointment' => $appointment,
            'lessonStatusOptions' => Appointment::lessonStatusOptions(),
            'vehicleCategoryFilter' => (string) request()->input('vehicle_category', ''),
        ])->render();
    }
}
