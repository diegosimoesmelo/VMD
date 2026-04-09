@php
    $slotKey = $day->format('Y-m-d').' '.$slot;
    $deleteFormId = 'delete_'.md5($slotKey);
    $busyTeacherIds = collect($busyTeacherIdsBySlot->get($slotKey, []));
    $busyStudentIds = collect($busyStudentIdsBySlot->get($slotKey, []));
    $availableTeachers = $teachers->filter(fn ($teacher) => $teacher->supportsTimeSlot($slot)
        && (! $busyTeacherIds->contains($teacher->id) || $appointment?->teacher_id === $teacher->id)
    );
    $availableStudents = $students->filter(fn ($student) => ! $busyStudentIds->contains($student->id)
        || $appointment?->student_id === $student->id
    );
    if ($appointment?->student && ! $availableStudents->contains(fn ($student) => $student->id === $appointment->student_id)) {
        $availableStudents = $availableStudents->push($appointment->student);
    }
@endphp

<div class="slot-card {{ $appointment?->type === \App\Models\Appointment::TYPE_LESSON ? 'busy' : '' }} {{ $appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE ? 'unavailable' : '' }}">
    @if ($appointment)
        <span class="slot-status {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'lesson' : 'unavailable' }}">
            {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'Aula marcada' : 'Indisponível' }}
        </span>
        <div class="slot-meta">
            <strong>{{ $appointment->teacher?->nome ?: 'Professor não informado' }}</strong>
            @if ($appointment->student)
                <span class="muted">
                    {{ $appointment->student->nome }}
                    @if ($appointment->lesson_category)
                        - aula {{ $appointment->lesson_category }}
                    @endif
                </span>
            @else
                <span class="muted">Veículo indisponível</span>
            @endif
        </div>
        @if ($appointment->notes)
            <p>{{ $appointment->notes }}</p>
        @endif
    @else
        <span class="slot-status free">Horário livre</span>
    @endif

    <form class="slot-form" method="POST" action="{{ route('appointments.store') }}">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $selectedVehicle->id }}">
        <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
        <input type="hidden" name="slot_date" value="{{ $day->toDateString() }}">
        <input type="hidden" name="slot_time" value="{{ $slot }}">

        <select name="type">
            <option value="{{ \App\Models\Appointment::TYPE_LESSON }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_LESSON)>Aula com aluno</option>
            <option value="{{ \App\Models\Appointment::TYPE_UNAVAILABLE }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE)>Indisponibilidade</option>
        </select>

        <select name="teacher_id" required>
            <option value="">Selecione o professor</option>
            @foreach ($availableTeachers as $teacher)
                <option value="{{ $teacher->id }}" @selected($appointment?->teacher_id === $teacher->id)>
                    {{ $teacher->nome }}
                </option>
            @endforeach
        </select>

        <select name="student_id">
            <option value="">Selecione um aluno</option>
            @foreach ($availableStudents as $student)
                @php
                    $studentTeacherLabel = $student->teacher ? 'professor: '.$student->teacher->nome : 'sem professor';
                    $studentLessonLabel = $studentCategoryLabels[$student->categoria_pretendida] ?? 'Categoria não informada';
                    $remainingLessons = $student->remainingLessonsForCategory($selectedVehicle->categoria);
                @endphp
                <option value="{{ $student->id }}" @selected($appointment?->student_id === $student->id)>
                    {{ $student->nome }} - {{ $studentLessonLabel }} - restam {{ $remainingLessons ?? 0 }} aulas {{ $selectedVehicle->categoria }} - {{ $studentTeacherLabel }}
                </option>
            @endforeach
        </select>

        <textarea name="notes" placeholder="Observações do horário">{{ $appointment?->notes }}</textarea>

        <div class="slot-actions">
            <button class="btn" type="submit">{{ $appointment ? 'Atualizar' : 'Salvar' }}</button>
            @if ($appointment)
                <button class="btn-secondary" type="submit" form="{{ $deleteFormId }}">Liberar</button>
            @endif
        </div>
    </form>

    @if ($appointment)
        <form class="slot-delete-form" id="{{ $deleteFormId }}" method="POST" action="{{ route('appointments.destroy', $appointment) }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
        </form>
    @endif
</div>
