@php
    $slotKey = $day->format('Y-m-d').' '.$slot;
    $deleteFormId = 'delete_'.md5($slotKey);
    $scheduleMode = $scheduleMode ?? 'vehicle';
    $slotAppointments = collect($slotAppointments ?? []);
    $selectedTeacher = $selectedTeacher ?? null;
    $selectedVehicle = $selectedVehicle ?? null;
    $vehicles = collect($vehicles ?? []);
    $teacherModeAllVehicles = $scheduleMode === 'teacher' && $selectedTeacher && ! $selectedVehicle;

    $teacherAppointment = $scheduleMode === 'teacher' && $selectedTeacher
        ? $slotAppointments->first(fn ($item) => (int) $item->teacher_id === (int) $selectedTeacher->id)
        : null;
    $vehicleAppointment = $selectedVehicle
        ? $slotAppointments->first(fn ($item) => (int) $item->vehicle_id === (int) $selectedVehicle->id)
        : null;
    $matchingAppointment = $scheduleMode === 'teacher' && $selectedTeacher && $selectedVehicle
        ? $slotAppointments->first(fn ($item) => (int) $item->teacher_id === (int) $selectedTeacher->id
            && (int) $item->vehicle_id === (int) $selectedVehicle->id)
        : null;

    if ($teacherModeAllVehicles) {
        $appointment = $teacherAppointment ?: null;
    } elseif ($scheduleMode === 'teacher' && $selectedTeacher) {
        $appointment = $matchingAppointment ?: ($teacherAppointment ?: ($vehicleAppointment ?: $appointment));
    }

    $busyTeacherIds = collect($busyTeacherIdsBySlot->get($slotKey, []));
    $busyStudentIds = collect($busyStudentIdsBySlot->get($slotKey, []));
    $busyVehicleIds = collect(($busyVehicleIdsBySlot ?? collect())->get($slotKey, []));
    $slotLocked = false;
    $lockReason = null;
    $slotVehicle = $teacherModeAllVehicles
        ? $appointment?->vehicle
        : $selectedVehicle;

    if ($teacherModeAllVehicles) {
        $availableVehicles = $vehicles
            ->filter(fn ($vehicle) => ! $busyVehicleIds->contains($vehicle->id) || $appointment?->vehicle_id === $vehicle->id)
            ->values();

        if (! $selectedTeacher->supportsTimeSlot($slot)) {
            $slotLocked = true;
            $lockReason = 'Fora do turno do instrutor';
        } elseif (! $appointment && $availableVehicles->isEmpty()) {
            $slotLocked = true;
            $lockReason = 'Nenhum veiculo livre neste horario';
        }
    } else {
        $availableVehicles = collect();

        if ($scheduleMode === 'teacher' && $selectedTeacher) {
            if (! $selectedTeacher->supportsTimeSlot($slot)) {
                $slotLocked = true;
                $lockReason = 'Fora do turno do instrutor';
            } elseif ($teacherAppointment && ! $matchingAppointment) {
                $slotLocked = true;
                $lockReason = 'Instrutor ocupado em outro veiculo: '.strtoupper($teacherAppointment->vehicle?->placa ?? '');
            } elseif ($vehicleAppointment && ! $matchingAppointment) {
                $slotLocked = true;
                $lockReason = 'Veiculo ocupado com outro instrutor: '.($vehicleAppointment->teacher?->nome ?? 'nao informado');
            }
        }
    }

    $availableTeachers = $teachers->filter(fn ($teacher) => $teacher->supportsTimeSlot($slot)
        && (! $busyTeacherIds->contains($teacher->id) || $appointment?->teacher_id === $teacher->id)
    );

    $availableStudents = $students->filter(function ($student) use ($busyStudentIds, $appointment, $teacherModeAllVehicles, $slotVehicle) {
        if ($busyStudentIds->contains($student->id) && $appointment?->student_id !== $student->id) {
            return false;
        }

        if (! $teacherModeAllVehicles || ! $slotVehicle) {
            return true;
        }

        return $student->supportsLessonCategory($slotVehicle->categoria)
            && ($student->hasRemainingLessonsForCategory($slotVehicle->categoria) || $appointment?->student_id === $student->id);
    });

    if ($appointment?->student && ! $availableStudents->contains(fn ($student) => $student->id === $appointment->student_id)) {
        $availableStudents = $availableStudents->push($appointment->student);
    }

    $isTrainingStudentAppointment = $appointment?->type === \App\Models\Appointment::TYPE_LESSON
        && (bool) $appointment?->student?->treinamento_para_habilitados;
@endphp

<div class="slot-card {{ $appointment?->type === \App\Models\Appointment::TYPE_LESSON ? 'busy' : '' }} {{ $isTrainingStudentAppointment ? 'training-student' : '' }} {{ $appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE ? 'unavailable' : '' }} {{ $slotLocked ? 'locked' : '' }}">
    @if ($slotLocked)
        <span class="slot-status locked">Horario ocupado</span>
        <div class="slot-meta">
            <strong>{{ $lockReason }}</strong>
            @if ($appointment?->student)
                <span class="muted">
                    {{ $appointment->student->nome }}
                    @if ($appointment->lesson_category)
                        - aula {{ $appointment->lesson_category }}
                    @endif
                </span>
            @elseif ($appointment)
                <span class="muted">Indisponibilidade registrada</span>
            @endif
        </div>
    @elseif ($appointment)
        <span class="slot-status {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'lesson' : 'unavailable' }}">
            {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'Aula marcada' : 'Indisponivel' }}
        </span>
        <div class="slot-meta">
            <strong>{{ $appointment->teacher?->nome ?: 'Professor nao informado' }}</strong>
            @if ($appointment->vehicle)
                <span class="muted">Veiculo {{ strtoupper($appointment->vehicle->placa) }} - categoria {{ $appointment->vehicle->categoria }}</span>
            @endif
            @if ($appointment->student)
                <span class="muted">
                    {{ $appointment->student->nome }}
                    @if ($appointment->lesson_category)
                        - aula {{ $appointment->lesson_category }}
                    @endif
                </span>
            @else
                <span class="muted">Veiculo indisponivel</span>
            @endif
        </div>
        @if ($appointment->notes)
            <p>{{ $appointment->notes }}</p>
        @endif
    @else
        <span class="slot-status free">Horario livre</span>
    @endif

    @unless ($slotLocked)
        <form class="slot-form" method="POST" action="{{ route('appointments.store') }}" @if ($teacherModeAllVehicles) data-full-submit @endif>
            @csrf
            @if ($teacherModeAllVehicles)
                <input type="hidden" name="teacher_mode_all_vehicles" value="1">
                @if ($appointment)
                    <input type="hidden" name="vehicle_id" value="{{ $appointment->vehicle_id }}">
                    <div class="slot-fixed-field">Veiculo {{ strtoupper($appointment->vehicle?->placa ?? '') }}</div>
                @else
                    <select name="vehicle_id" class="slot-vehicle-select" required>
                        <option value="">Selecione o veiculo</option>
                        @foreach ($availableVehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" data-category="{{ $vehicle->categoria }}">
                                {{ strtoupper($vehicle->placa) }} - categoria {{ $vehicle->categoria }}
                            </option>
                        @endforeach
                    </select>
                @endif
            @else
                <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
            @endif
            <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
            <input type="hidden" name="schedule_mode" value="{{ $scheduleMode }}">
            <input type="hidden" name="teacher" value="{{ $selectedTeacher?->id }}">
            <input type="hidden" name="slot_date" value="{{ $day->toDateString() }}">
            <input type="hidden" name="slot_time" value="{{ $slot }}">

            <select name="type">
                <option value="{{ \App\Models\Appointment::TYPE_LESSON }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_LESSON)>Aula com aluno</option>
                <option value="{{ \App\Models\Appointment::TYPE_UNAVAILABLE }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE)>Indisponibilidade</option>
            </select>

            @if ($scheduleMode === 'teacher' && $selectedTeacher)
                <input type="hidden" name="teacher_id" value="{{ $selectedTeacher->id }}">
                <div class="slot-fixed-field">{{ $selectedTeacher->nome }}</div>
            @else
                <select name="teacher_id" required>
                    <option value="">Selecione o professor</option>
                    @foreach ($availableTeachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($appointment?->teacher_id === $teacher->id)>
                            {{ $teacher->nome }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="student_id" class="{{ $teacherModeAllVehicles ? 'slot-student-select' : '' }}">
                <option value="">Selecione um aluno</option>
                @foreach ($availableStudents as $student)
                    @php
                        $studentTeacherLabel = $student->teacher ? 'professor: '.$student->teacher->nome : 'sem professor';
                        $studentLessonLabel = $studentCategoryLabels[$student->categoria_pretendida] ?? 'Categoria nao informada';
                        $studentCategories = collect(['A', 'B'])
                            ->filter(fn ($category) => $student->supportsLessonCategory($category))
                            ->implode(',');
                        $remainingText = $teacherModeAllVehicles && ! $slotVehicle
                            ? 'A: '.($student->remainingLessonsForCategory('A') ?? 0).' | B: '.($student->remainingLessonsForCategory('B') ?? 0)
                            : 'restam '.($slotVehicle ? ($student->remainingLessonsForCategory($slotVehicle->categoria) ?? 0) : 0).' aulas '.($slotVehicle?->categoria ?? '');
                    @endphp
                    <option value="{{ $student->id }}" data-categories="{{ $studentCategories }}" @selected($appointment?->student_id === $student->id)>
                        {{ $student->nome }} - {{ $studentLessonLabel }} - {{ $remainingText }} - {{ $studentTeacherLabel }}
                    </option>
                @endforeach
            </select>

            <textarea name="notes" placeholder="Observacoes do horario">{{ $appointment?->notes }}</textarea>

            <div class="slot-actions">
                <button class="btn" type="submit">{{ $appointment ? 'Atualizar' : 'Salvar' }}</button>
                @if ($appointment)
                    <button class="btn-secondary" type="submit" form="{{ $deleteFormId }}">Liberar</button>
                @endif
            </div>
        </form>

        @if ($appointment)
            <form class="slot-delete-form" id="{{ $deleteFormId }}" method="POST" action="{{ route('appointments.destroy', $appointment) }}" @if ($teacherModeAllVehicles) data-full-submit @endif>
                @csrf
                @method('DELETE')
                <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
                <input type="hidden" name="schedule_mode" value="{{ $scheduleMode }}">
                <input type="hidden" name="teacher" value="{{ $selectedTeacher?->id }}">
                @if ($teacherModeAllVehicles)
                    <input type="hidden" name="teacher_mode_all_vehicles" value="1">
                @endif
            </form>
        @endif
    @endunless
</div>
