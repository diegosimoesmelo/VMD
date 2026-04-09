@php
    $effectiveStatus = $appointment->effectiveLessonStatus();
    $statusClass = match ($effectiveStatus) {
        \App\Models\Appointment::LESSON_STATUS_COMPLETED => 'completed',
        \App\Models\Appointment::LESSON_STATUS_STUDENT_ABSENT => 'absent',
        \App\Models\Appointment::LESSON_STATUS_VEHICLE_ISSUE => 'vehicle-issue',
        default => 'scheduled',
    };
@endphp

<div class="monitor-slot-card {{ $appointment->type === \App\Models\Appointment::TYPE_UNAVAILABLE ? 'unavailable' : '' }}">
    @if ($appointment->type === \App\Models\Appointment::TYPE_LESSON)
        <span class="monitor-status {{ $statusClass }}">{{ $appointment->effectiveLessonStatusLabel() }}</span>
        <div class="monitor-meta">
            <strong>{{ $appointment->student?->nome ?: 'Aluno não informado' }}</strong>
            <span>{{ $appointment->teacher?->nome ?: 'Professor não informado' }}</span>
            <span>Veículo {{ strtoupper($appointment->vehicle?->placa ?? '') }}</span>
        </div>

        <form class="lesson-monitoring-form" method="POST" action="{{ route('lesson-monitoring.update', $appointment) }}">
            @csrf
            <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">

            <label for="lesson_status_{{ $appointment->id }}">Apontamento</label>
            <select id="lesson_status_{{ $appointment->id }}" name="lesson_status">
                <option value="">Sem apontamento manual</option>
                @foreach ($lessonStatusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($appointment->lesson_status === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <label for="lesson_status_notes_{{ $appointment->id }}">Observação operacional</label>
            <textarea id="lesson_status_notes_{{ $appointment->id }}" name="lesson_status_notes" placeholder="Detalhes do ocorrido">{{ $appointment->lesson_status_notes }}</textarea>

            <div class="monitor-actions">
                <button class="btn-secondary" type="submit">Salvar apontamento</button>
            </div>
        </form>
    @elseif ($appointment->type === \App\Models\Appointment::TYPE_UNAVAILABLE)
        <span class="monitor-status unavailable">Indisponível</span>
        <div class="monitor-meta">
            <strong>Veículo bloqueado neste horário</strong>
            <span>Sem aula para acompanhamento.</span>
        </div>
    @endif
</div>

