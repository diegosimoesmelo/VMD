@extends('layouts.panel', ['title' => 'Agenda semanal'])

@section('content')
    @php
        $weekDayLabels = [
            1 => 'Segunda-feira',
            2 => 'Terca-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sabado',
        ];
        $studentCategoryLabels = [
            'A' => 'Aula A',
            'B' => 'Aula B',
            'AB' => 'Aula A ou B',
        ];
    @endphp

    <style>
        .agenda-toolbar {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 20px;
        }
        .agenda-toolbar form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: end;
            width: 100%;
        }
        .agenda-toolbar .field-inline {
            min-width: 220px;
            flex: 1 1 220px;
        }
        .teacher-category-summary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .teacher-category-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(217, 119, 6, 0.12);
            color: #9a3412;
            font-size: 12px;
            font-weight: 700;
        }
        .agenda-week-nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 18px;
        }
        .agenda-grid-wrap {
            overflow-x: auto;
        }
        .agenda-grid {
            width: 100%;
            min-width: 1120px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .agenda-grid th,
        .agenda-grid td {
            padding: 14px;
            border-bottom: 1px solid rgba(20, 33, 61, 0.08);
            border-right: 1px solid rgba(20, 33, 61, 0.05);
            vertical-align: top;
        }
        .agenda-grid th {
            background: rgba(20, 33, 61, 0.04);
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 700;
        }
        .agenda-time {
            width: 110px;
            white-space: nowrap;
            font-weight: 700;
            color: var(--color-secondary);
        }
        .slot-card {
            border-radius: 18px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(20, 33, 61, 0.10);
            min-height: 140px;
            display: grid;
            gap: 10px;
        }
        .slot-card.busy {
            background: rgba(217, 119, 6, 0.08);
            border-color: rgba(217, 119, 6, 0.20);
        }
        .slot-card.unavailable {
            background: rgba(20, 33, 61, 0.08);
            border-color: rgba(20, 33, 61, 0.14);
        }
        .slot-card.blocked {
            background: rgba(148, 163, 184, 0.10);
            border-color: rgba(148, 163, 184, 0.24);
        }
        .slot-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            width: fit-content;
            font-size: 12px;
            font-weight: 700;
        }
        .slot-status.free {
            background: rgba(34, 197, 94, 0.12);
            color: #166534;
        }
        .slot-status.lesson {
            background: rgba(217, 119, 6, 0.12);
            color: #9a3412;
        }
        .slot-status.unavailable {
            background: rgba(20, 33, 61, 0.10);
            color: var(--color-secondary);
        }
        .slot-status.blocked {
            background: rgba(148, 163, 184, 0.18);
            color: #475569;
        }
        .slot-meta strong {
            display: block;
            color: var(--color-secondary);
            margin-bottom: 4px;
        }
        .slot-form {
            display: grid;
            gap: 8px;
        }
        .slot-form select,
        .slot-form textarea {
            margin-bottom: 0;
            padding: 10px 12px;
            font-size: 14px;
        }
        .slot-form textarea {
            min-height: 76px;
        }
        .slot-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .slot-actions .btn,
        .slot-actions .btn-secondary {
            padding: 10px 12px;
            font-size: 13px;
        }
        .empty-agenda {
            padding: 32px;
            text-align: center;
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Agenda de professores</span>
            <h1>Planejamento semanal de segunda a sabado</h1>
            <p>Controle aulas e indisponibilidades por professor, em slots fixos de 50 minutos, com categoria da aula e respeito ao turno disponivel.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $teachers->count() }}</strong>
                    <span>professores no filtro</span>
                </div>
                <div class="stat-chip">
                    <strong>11</strong>
                    <span>slots por dia</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="notice notice-error">{{ $errors->first() }}</p>
    @endif

    <div class="surface-card section-card">
        <div class="agenda-toolbar">
            <form method="GET" action="{{ route('appointments.index') }}">
                <div class="field-inline">
                    <label for="teacher_category">Categoria ensinada</label>
                    <select id="teacher_category" name="teacher_category">
                        <option value="">Todas</option>
                        @foreach ($teacherCategoryOptions as $category)
                            <option value="{{ $category }}" @selected($teacherCategoryFilter === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="teacher">Professor</label>
                    <select id="teacher" name="teacher" required>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($selectedTeacher?->id === $teacher->id)>{{ $teacher->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="week_start">Semana de referencia</label>
                    <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
                </div>
                <button class="btn" type="submit">Carregar agenda</button>
            </form>
        </div>

        @if ($selectedTeacher)
            <div class="teacher-category-summary">
                @foreach ($selectedTeacher->categorias_ensino ?? [] as $category)
                    <span class="teacher-category-chip">Categoria {{ $category }}</span>
                @endforeach
                @foreach ($selectedTeacher->turnos_disponiveis ?? [] as $shift)
                    <span class="teacher-category-chip">{{ \App\Models\Teacher::shiftOptions()[$shift] ?? ucfirst($shift) }}</span>
                @endforeach
            </div>

            <div class="agenda-week-nav">
                <a class="btn-secondary" href="{{ route('appointments.index', ['teacher' => $selectedTeacher->id, 'teacher_category' => $teacherCategoryFilter, 'week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}">Semana anterior</a>
                <span><strong>{{ $selectedTeacher->nome }}</strong> - {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                <a class="btn-secondary" href="{{ route('appointments.index', ['teacher' => $selectedTeacher->id, 'teacher_category' => $teacherCategoryFilter, 'week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}">Proxima semana</a>
            </div>

            <div class="agenda-grid-wrap">
                <table class="agenda-grid">
                    <thead>
                        <tr>
                            <th class="agenda-time">Horario</th>
                            @foreach ($weekDays as $day)
                                <th>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('d/m') }}<br><span class="muted">{{ $day->format('d/m') }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($timeSlots as $slot)
                            <tr>
                                <td class="agenda-time">{{ $slot }}</td>
                                @foreach ($weekDays as $day)
                                    @php
                                        $slotKey = $day->format('Y-m-d').' '.$slot;
                                        $appointment = $appointmentsBySlot->get($slotKey);
                                        $deleteFormId = 'delete_'.md5($slotKey);
                                        $slotSupported = $selectedTeacher->supportsTimeSlot($slot);
                                    @endphp
                                    <td>
                                        <div class="slot-card {{ $appointment?->type === \App\Models\Appointment::TYPE_LESSON ? 'busy' : '' }} {{ $appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE ? 'unavailable' : '' }} {{ ! $appointment && ! $slotSupported ? 'blocked' : '' }}">
                                            @if ($appointment)
                                                <span class="slot-status {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'lesson' : 'unavailable' }}">
                                                    {{ $appointment->type === \App\Models\Appointment::TYPE_LESSON ? 'Aula marcada' : 'Indisponivel' }}
                                                </span>
                                                <div class="slot-meta">
                                                    @if ($appointment->student)
                                                        <strong>{{ $appointment->student->nome }}</strong>
                                                        <span class="muted">
                                                            {{ $appointment->student->cpf }}
                                                            @if ($appointment->lesson_category)
                                                                - aula {{ $appointment->lesson_category }}
                                                            @endif
                                                            @if ($appointment->vehicle)
                                                                - veiculo {{ strtoupper($appointment->vehicle->placa) }}
                                                            @endif
                                                        </span>
                                                    @else
                                                        <strong>Professor indisponivel</strong>
                                                    @endif
                                                </div>
                                                @if ($appointment->notes)
                                                    <p>{{ $appointment->notes }}</p>
                                                @endif
                                            @elseif (! $slotSupported)
                                                <span class="slot-status blocked">Fora do turno</span>
                                                <p>Este horario nao esta disponivel para os turnos marcados no cadastro do professor.</p>
                                            @else
                                                <span class="slot-status free">Horario livre</span>
                                            @endif

                                            @if ($appointment || $slotSupported)
                                                <form class="slot-form" method="POST" action="{{ route('appointments.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="teacher_id" value="{{ $selectedTeacher->id }}">
                                                    <input type="hidden" name="teacher_category" value="{{ $teacherCategoryFilter }}">
                                                    <input type="hidden" name="slot_date" value="{{ $day->toDateString() }}">
                                                    <input type="hidden" name="slot_time" value="{{ $slot }}">

                                                    <select name="type">
                                                        <option value="{{ \App\Models\Appointment::TYPE_LESSON }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_LESSON)>Aula com aluno</option>
                                                        <option value="{{ \App\Models\Appointment::TYPE_UNAVAILABLE }}" @selected($appointment?->type === \App\Models\Appointment::TYPE_UNAVAILABLE)>Indisponibilidade</option>
                                                    </select>

                                                    <select name="student_id">
                                                        <option value="">Selecione um aluno</option>
                                                        @foreach ($students as $student)
                                                            @php
                                                                $isLinkedToSelectedTeacher = (int) $student->teacher_id === (int) $selectedTeacher->id;
                                                                $studentTeacherLabel = $student->teacher
                                                                    ? ($isLinkedToSelectedTeacher ? 'vinculado a este professor' : 'professor: '.$student->teacher->nome)
                                                                    : 'sem professor';
                                                                $studentLessonLabel = $studentCategoryLabels[$student->categoria_pretendida] ?? 'Categoria nao informada';
                                                            @endphp
                                                            <option
                                                                value="{{ $student->id }}"
                                                                style="{{ $isLinkedToSelectedTeacher ? 'background:#dcfce7;color:#166534;font-weight:700;' : 'background:#ffffff;color:#172033;' }}"
                                                                @selected($appointment?->student_id === $student->id)
                                                            >
                                                                {{ $student->nome }} - {{ $studentLessonLabel }} - {{ $studentTeacherLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <select name="vehicle_id">
                                                        <option value="">Selecione um veiculo</option>
                                                        @foreach ($selectedTeacher->vehicles->sortBy('placa') as $vehicle)
                                                            <option value="{{ $vehicle->id }}" @selected($appointment?->vehicle_id === $vehicle->id)>
                                                                {{ strtoupper($vehicle->placa) }} - {{ \App\Models\Vehicle::categoryOptions()[$vehicle->categoria] ?? $vehicle->categoria }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <select name="lesson_category">
                                                        <option value="">Categoria da aula</option>
                                                        @foreach ($selectedTeacher->schedulableCategories() as $category)
                                                            <option value="{{ $category }}" @selected($appointment?->lesson_category === $category)>{{ $category }}</option>
                                                        @endforeach
                                                    </select>

                                                    <textarea name="notes" placeholder="Observacoes do horario">{{ old('notes', $appointment?->notes) }}</textarea>

                                                    <div class="slot-actions">
                                                        <button class="btn" type="submit">{{ $appointment ? 'Atualizar' : 'Salvar' }}</button>
                                                        @if ($appointment)
                                                            <button class="btn-secondary" type="submit" form="{{ $deleteFormId }}">Liberar</button>
                                                        @endif
                                                    </div>
                                                </form>
                                            @endif

                                            @if ($appointment)
                                                <form id="{{ $deleteFormId }}" method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="teacher_category" value="{{ $teacherCategoryFilter }}">
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-agenda">
                <strong>Nenhum professor encontrado.</strong>
                <p>Ajuste o filtro de categoria ou cadastre professores para montar a agenda semanal.</p>
            </div>
        @endif
    </div>
@endsection
