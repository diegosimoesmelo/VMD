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
        $studentCategoryLabels = \App\Models\Student::lessonCategoryLabels();
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
        .vehicle-summary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .vehicle-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
            gap: 18px;
            align-items: stretch;
            margin-bottom: 22px;
        }
        .vehicle-hero-card {
            padding: 24px 26px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.24), transparent 38%),
                linear-gradient(135deg, rgba(var(--color-secondary-rgb), 0.98), rgba(var(--color-secondary-rgb), 0.84));
            color: #fff;
            box-shadow: 0 24px 46px rgba(var(--color-secondary-rgb), 0.18);
        }
        .vehicle-hero-card h2,
        .vehicle-hero-card p {
            color: #fff;
        }
        .vehicle-hero-card p {
            max-width: 540px;
            opacity: 0.82;
        }
        .vehicle-plate {
            display: inline-flex;
            align-items: center;
            padding: 14px 20px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: clamp(28px, 5vw, 46px);
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            margin: 14px 0 12px;
        }
        .vehicle-hero-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .vehicle-hero-chip {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 12px;
            font-weight: 700;
        }
        .vehicle-hero-side {
            display: grid;
            gap: 14px;
        }
        .vehicle-focus-card {
            padding: 20px 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            box-shadow: var(--shadow-card);
        }
        .vehicle-focus-card strong {
            display: block;
            color: var(--color-secondary);
            font-size: 24px;
            margin-top: 6px;
        }
        .vehicle-chip {
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
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            border-right: 1px solid rgba(var(--color-secondary-rgb), 0.05);
            vertical-align: top;
        }
        .agenda-grid th {
            background: rgba(var(--color-secondary-rgb), 0.04);
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 700;
        }
        .agenda-time {
            width: 140px;
            white-space: nowrap;
            font-weight: 700;
            color: var(--color-secondary);
        }
        .slot-card {
            border-radius: 18px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
            min-height: 160px;
            display: grid;
            gap: 10px;
        }
        .slot-card.busy {
            background: rgba(217, 119, 6, 0.08);
            border-color: rgba(217, 119, 6, 0.20);
        }
        .slot-card.unavailable {
            background: rgba(var(--color-secondary-rgb), 0.08);
            border-color: rgba(var(--color-secondary-rgb), 0.14);
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
            background: rgba(var(--color-secondary-rgb), 0.10);
            color: var(--color-secondary);
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
        @media (max-width: 980px) {
            .vehicle-hero {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Agenda de veiculos</span>
            <h1>Planejamento semanal orientado pelo veiculo</h1>
            <p>O agendamento principal agora usa a grade do veiculo. Em cada slot voce escolhe o professor da aula e acompanha a agenda semanal resumida de cada professor com aluno e veiculo.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $vehicles->count() }}</strong>
                    <span>veiculos no filtro</span>
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
                    <label for="vehicle_category">Categoria do veiculo</label>
                    <select id="vehicle_category" name="vehicle_category">
                        <option value="">Todas</option>
                        @foreach ($vehicleCategoryOptions as $value => $label)
                            <option value="{{ $value }}" @selected($vehicleCategoryFilter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-inline">
                    <label for="vehicle">Veiculo</label>
                    <select id="vehicle" name="vehicle" required>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected($selectedVehicle?->id === $vehicle->id)>
                                {{ strtoupper($vehicle->placa) }}
                            </option>
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

        @if ($selectedVehicle)
            <div class="vehicle-hero">
                <div class="vehicle-hero-card">
                    <span class="eyebrow">Veiculo em foco</span>
                    <h2>Voce esta montando a agenda deste veiculo</h2>
                    <div class="vehicle-plate">{{ strtoupper($selectedVehicle->placa) }}</div>
                    <p>Todos os horarios desta grade pertencem exclusivamente a este veiculo. Qualquer aula marcada aqui reserva esta placa para o horario selecionado.</p>
                    <div class="vehicle-hero-meta">
                        <span class="vehicle-hero-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
                        <span class="vehicle-hero-chip">Semana {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="vehicle-hero-side">
                    <div class="vehicle-focus-card">
                        <span class="eyebrow">Leitura Rapida</span>
                        <strong>{{ strtoupper($selectedVehicle->placa) }}</strong>
                        <p>Use essa referencia antes de salvar qualquer aula para evitar marcar no carro errado.</p>
                    </div>
                    <div class="vehicle-focus-card">
                        <span class="eyebrow">Categoria</span>
                        <strong>{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</strong>
                        <p>Somente professores e alunos compativeis com esta categoria entram no fluxo principal.</p>
                    </div>
                </div>
            </div>

            <div class="vehicle-summary">
                <span class="vehicle-chip">Placa {{ strtoupper($selectedVehicle->placa) }}</span>
                <span class="vehicle-chip">{{ \App\Models\Vehicle::categoryOptions()[$selectedVehicle->categoria] ?? $selectedVehicle->categoria }}</span>
            </div>

            <div class="agenda-week-nav">
                <a class="btn-secondary" href="{{ route('appointments.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}">Semana anterior</a>
                <span><strong>{{ strtoupper($selectedVehicle->placa) }}</strong> - {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</span>
                <a class="btn-secondary" href="{{ route('appointments.index', ['vehicle' => $selectedVehicle->id, 'vehicle_category' => $vehicleCategoryFilter, 'week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}">Proxima semana</a>
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
                                        $eligibleTeachers = $teachers->filter(fn ($teacher) => $teacher->supportsTimeSlot($slot));
                                    @endphp
                                    <td>
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

                                                <textarea name="notes" placeholder="Observacoes do horario">{{ old('notes', $appointment?->notes) }}</textarea>

                                                <div class="slot-actions">
                                                    <button class="btn" type="submit">{{ $appointment ? 'Atualizar' : 'Salvar' }}</button>
                                                    @if ($appointment)
                                                        <button class="btn-secondary" type="submit" form="{{ $deleteFormId }}">Liberar</button>
                                                    @endif
                                                </div>
                                            </form>

                                            @if ($appointment)
                                                <form id="{{ $deleteFormId }}" method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="vehicle_category" value="{{ $vehicleCategoryFilter }}">
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
                <strong>Nenhum veiculo encontrado.</strong>
                <p>Ajuste o filtro de categoria ou cadastre veiculos para montar a agenda semanal.</p>
            </div>
        @endif
    </div>

@endsection
