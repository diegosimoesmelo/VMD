@php
    $slotKey = $day->format('Y-m-d').' '.$slot;
    $deleteFormId = 'delete_'.md5($slotKey);
    $eligibleTeachers = $teachers->filter(fn ($teacher) => $teacher->supportsTimeSlot($slot));
@endphp

<div class="slot-card {{ $appointment?->type === \App\Models\Appointment::TYPE_LESSON ? 'busy' : '' }} {{ $appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE ? 'unavailable' : '' }}">
    @if ($appointment)
        <span class="slot-status {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'lesson' : 'unavailable' }}">
            {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'Aula marcada' : 'Indisponivel' }}
        </span>
        <div class="slot-meta">
            <strong>{{ $appointment->teacher?->nome ?: 'Professor nao informado' }}</strong>
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
            @foreach ($eligibleTeachers as $teacher)
                <option value="{{ $teacher->id }}" @selected($appointment?->teacher_id === $teacher->id)>
                    {{ $teacher->nome }}
                </option>
            @endforeach
        </select>

        <select name="student_id">
            <option value="">Selecione um aluno</option>
            @foreach ($students as $student)
                @php
                    $studentTeacherLabel = $student->teacher ? 'professor: '.$student->teacher->nome : 'sem professor';
                    $studentLessonLabel = $studentCategoryLabels[$student->categoria_pretendida] ?? 'Categoria nao informada';
                @endphp
                <option value="{{ $student->id }}" @selected($appointment?->student_id === $student->id)>
                    {{ $student->nome }} - {{ $studentLessonLabel }} - {{ $studentTeacherLabel }}
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
        <form class="slot-delete-form" id="{{ $deleteFormId }}" method="POST" action="{{ route('appointments.destroy', $appointment) }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
        </form>
    @endif
</div>
