@extends('layouts.panel', ['title' => 'Inicio'])

@section('content')
    @if (($mode ?? 'administrative') === 'teacher')
        @php
            $weekDayLabels = [
                1 => 'Segunda-feira',
                2 => 'Terca-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sabado',
                7 => 'Domingo',
            ];
        @endphp
        <style>
            .teacher-shell {
                display: grid;
                gap: 18px;
            }
            .teacher-hero {
                padding: 24px;
                border-radius: 28px;
                background:
                    radial-gradient(circle at top right, rgba(255, 255, 255, 0.20), transparent 38%),
                    linear-gradient(145deg, rgba(var(--color-secondary-rgb), 0.98), rgba(var(--color-secondary-rgb), 0.86));
                color: #fff;
                box-shadow: var(--shadow-card);
            }
            .teacher-hero h1,
            .teacher-hero p {
                color: #fff;
            }
            .teacher-kpis {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
            }
            .teacher-kpi {
                padding: 16px;
                border-radius: 20px;
                background: rgba(255, 255, 255, 0.10);
                border: 1px solid rgba(255, 255, 255, 0.14);
            }
            .teacher-kpi strong {
                display: block;
                font-size: 24px;
                margin-bottom: 6px;
            }
            .teacher-next,
            .teacher-day-card {
                padding: 20px;
                border-radius: 24px;
                background: rgba(255, 255, 255, 0.94);
                border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
                box-shadow: var(--shadow-card);
            }
            .teacher-day-grid {
                display: grid;
                gap: 14px;
            }
            .teacher-day-card h2 {
                margin-bottom: 6px;
            }
            .teacher-appointment-list {
                display: grid;
                gap: 10px;
            }
            .teacher-appointment-item {
                padding: 14px;
                border-radius: 18px;
                background: rgba(var(--color-secondary-rgb), 0.04);
                border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            }
            .teacher-appointment-item strong {
                display: block;
                color: var(--color-secondary);
                margin-bottom: 4px;
            }
            .teacher-empty {
                color: var(--color-muted-text);
                padding: 14px;
                border-radius: 18px;
                background: rgba(var(--color-secondary-rgb), 0.04);
            }
            @media (max-width: 900px) {
                .teacher-kpis {
                    grid-template-columns: 1fr;
                }
                .teacher-hero {
                    padding: 20px;
                }
            }
        </style>

        <div class="teacher-shell">
            <section class="teacher-hero">
                <span class="eyebrow">Painel do professor</span>
                <h1>{{ auth()->user()->name ?: auth()->user()->username }}</h1>
                @if ($teacher)
                    <p>Resumo da sua semana de aulas, pensado para consulta rapida no celular.</p>
                @else
                    <p>Seu acesso de professor ainda nao foi vinculado ao cadastro interno. Solicite ao gerente ou administrativo para revisar esse vinculo.</p>
                @endif

                @if ($teacher)
                    <div class="teacher-kpis">
                        <div class="teacher-kpi">
                            <strong>{{ $summary['total_week'] }}</strong>
                            <span>proximas aulas</span>
                        </div>
                        <div class="teacher-kpi">
                            <strong>{{ $summary['today'] }}</strong>
                            <span>aulas de hoje</span>
                        </div>
                        <div class="teacher-kpi">
                            <strong>{{ $weekStart->format('d/m') }}</strong>
                            <span>inicio da semana</span>
                        </div>
                    </div>
                @endif
            </section>

            @if ($teacher)
                <section class="teacher-next">
                    <h2>Proximo horario</h2>
                    @if ($summary['next'])
                        <p><strong>{{ $summary['next']->starts_at->format('d/m/Y H:i') }}</strong></p>
                        <p>Aluno: {{ $summary['next']->student?->nome ?: 'Nao informado' }}</p>
                        <p>Veiculo: {{ $summary['next']->vehicle ? strtoupper($summary['next']->vehicle->placa) : '-' }}</p>
                    @else
                        <p>Nenhuma aula futura nesta semana.</p>
                    @endif
                </section>

                <div class="teacher-day-grid">
                    @foreach ($weekDays as $day)
                        @php
                            $dayAppointments = $appointmentsByDay->get($day->toDateString(), collect());
                        @endphp
                        <section class="teacher-day-card">
                            <h2>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('l') }}</h2>
                            <p>{{ $day->format('d/m/Y') }}</p>

                            @if ($dayAppointments->isEmpty())
                                <div class="teacher-empty">Nenhuma aula agendada neste dia.</div>
                            @else
                                <div class="teacher-appointment-list">
                                    @foreach ($dayAppointments as $appointment)
                                        <div class="teacher-appointment-item">
                                            <strong>{{ $appointment->starts_at->format('H:i') }} ate {{ $appointment->ends_at?->format('H:i') }}</strong>
                                            <div>Aluno: {{ $appointment->student?->nome ?: 'Nao informado' }}</div>
                                            <div>Veiculo: {{ $appointment->vehicle ? strtoupper($appointment->vehicle->placa) : '-' }}</div>
                                            @if ($appointment->lesson_category)
                                                <div>Categoria: {{ $appointment->lesson_category }}</div>
                                            @endif
                                            @if ($appointment->notes)
                                                <div>Observacoes: {{ $appointment->notes }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        @php
            $weekDayLabels = [
                1 => 'Seg',
                2 => 'Ter',
                3 => 'Qua',
                4 => 'Qui',
                5 => 'Sex',
                6 => 'Sab',
            ];
        @endphp
        <style>
            .dashboard-week-nav {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
                align-items: center;
                margin-bottom: 22px;
            }
            .summary-grid {
                display: grid;
                gap: 20px;
            }
            .summary-card {
                padding: 22px;
            }
            .summary-header {
                display: flex;
                justify-content: space-between;
                gap: 14px;
                align-items: flex-start;
                margin-bottom: 18px;
                flex-wrap: wrap;
            }
            .summary-list {
                display: grid;
                gap: 18px;
            }
            .summary-item {
                padding: 18px;
                border-radius: 22px;
                background: rgba(var(--color-secondary-rgb), 0.03);
                border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            }
            .summary-item-title {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: center;
                margin-bottom: 12px;
                flex-wrap: wrap;
            }
            .summary-item-title strong {
                color: var(--color-secondary);
                font-size: 18px;
            }
            .mini-grid-wrap {
                overflow-x: auto;
            }
            .mini-grid {
                width: 100%;
                min-width: 860px;
                border-collapse: separate;
                border-spacing: 0;
            }
            .mini-grid th,
            .mini-grid td {
                padding: 10px;
                border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
                border-right: 1px solid rgba(var(--color-secondary-rgb), 0.05);
                vertical-align: top;
            }
            .mini-grid th {
                background: rgba(var(--color-secondary-rgb), 0.04);
                color: var(--color-secondary);
                font-size: 12px;
                font-weight: 700;
            }
            .mini-time {
                width: 90px;
                white-space: nowrap;
                font-weight: 700;
                color: var(--color-secondary);
            }
            .mini-cell {
                min-height: 58px;
                border-radius: 14px;
                padding: 8px 10px;
                background: rgba(var(--color-secondary-rgb), 0.04);
                font-size: 13px;
            }
            .mini-cell.busy {
                background: rgba(217, 119, 6, 0.08);
            }
            .mini-cell strong {
                display: block;
                color: var(--color-secondary);
                margin-bottom: 3px;
            }
            .mini-empty {
                color: var(--color-muted-text);
            }
        </style>

        <div class="page-header">
            <div class="header-copy">
                <span class="eyebrow">Painel administrativo</span>
                <h1>Inicio</h1>
                <p>Bem-vindo, {{ auth()->user()->name ?: auth()->user()->username }}. Perfil atual: {{ auth()->user()->roleLabel() }}.</p>
                <div class="header-stats">
                    <div class="stat-chip">
                        <strong>{{ $stats['students'] ?? 0 }}</strong>
                        <span>alunos</span>
                    </div>
                    <div class="stat-chip">
                        <strong>{{ $stats['teachers'] ?? 0 }}</strong>
                        <span>professores</span>
                    </div>
                    <div class="stat-chip">
                        <strong>{{ $stats['vehicles'] ?? 0 }}</strong>
                        <span>veiculos</span>
                    </div>
                    <div class="stat-chip">
                        <strong>{{ $stats['appointments_today'] ?? 0 }}</strong>
                        <span>aulas hoje</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-week-nav">
            <a class="btn-secondary" href="{{ route('dashboard', array_filter(['week_start' => $weekStart->copy()->subWeek()->toDateString(), 'teacher_id' => $teacherFilter, 'vehicle_id' => $vehicleFilter])) }}">Semana anterior</a>
            <span><strong>Semana {{ $weekStart->format('d/m') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</strong></span>
            <a class="btn-secondary" href="{{ route('dashboard', array_filter(['week_start' => $weekStart->copy()->addWeek()->toDateString(), 'teacher_id' => $teacherFilter, 'vehicle_id' => $vehicleFilter])) }}">Proxima semana</a>
        </div>

        <div class="surface-card section-card">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="form-grid">
                    <div class="field col-4">
                        <label for="week_start">Semana de referencia</label>
                        <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
                    </div>
                    <div class="field col-4">
                        <label for="teacher_id">Professor</label>
                        <select id="teacher_id" name="teacher_id">
                            <option value="">Todos</option>
                            @foreach ($allTeachers as $teacherOption)
                                <option value="{{ $teacherOption->id }}" @selected($teacherFilter === $teacherOption->id)>{{ $teacherOption->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="vehicle_id">Veiculo</label>
                        <select id="vehicle_id" name="vehicle_id">
                            <option value="">Todos</option>
                            @foreach ($allVehicles as $vehicleOption)
                                <option value="{{ $vehicleOption->id }}" @selected($vehicleFilter === $vehicleOption->id)>{{ strtoupper($vehicleOption->placa) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn" type="submit">Aplicar filtros</button>
                    <a class="btn-secondary" href="{{ route('dashboard') }}">Limpar</a>
                </div>
            </form>
        </div>

        <div class="summary-grid">
            <section class="surface-card summary-card">
                <div class="summary-header">
                    <div>
                        <h2>Resumo semanal dos professores</h2>
                        <p>Consulta rapida da agenda da equipe para a semana selecionada.</p>
                    </div>
                </div>

                <div class="summary-list">
                    @foreach ($teachers as $teacher)
                        <article class="summary-item">
                            <div class="summary-item-title">
                                <strong>{{ $teacher->nome }}</strong>
                                <span class="tag">{{ $teacher->schedulingStatusLabel() }}</span>
                            </div>
                            <div class="mini-grid-wrap">
                                <table class="mini-grid">
                                    <thead>
                                        <tr>
                                            <th class="mini-time">Horario</th>
                                            @foreach ($weekDays as $day)
                                                <th>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('d/m') }}<br><span class="muted">{{ $day->format('d/m') }}</span></th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($timeSlots as $slot)
                                            <tr>
                                                <td class="mini-time">{{ $slot }}</td>
                                                @foreach ($weekDays as $day)
                                                    @php
                                                        $appointment = $teacherSchedules[$teacher->id][$slot][$day->toDateString()] ?? null;
                                                    @endphp
                                                    <td>
                                                        <div class="mini-cell {{ $appointment ? 'busy' : '' }}">
                                                            @if ($appointment)
                                                                <strong>{{ $appointment->student?->nome ?: 'Indisponivel' }}</strong>
                                                                @if ($appointment->vehicle)
                                                                    <div>{{ strtoupper($appointment->vehicle->placa) }}</div>
                                                                @endif
                                                            @else
                                                                <span class="mini-empty">Livre</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="surface-card summary-card">
                <div class="summary-header">
                    <div>
                        <h2>Resumo semanal dos veiculos</h2>
                        <p>Grade consolidada por veiculo para leitura operacional da semana.</p>
                    </div>
                </div>

                <div class="summary-list">
                    @foreach ($vehicles as $vehicle)
                        <article class="summary-item">
                            <div class="summary-item-title">
                                <strong>{{ strtoupper($vehicle->placa) }}</strong>
                                <span class="tag">{{ \App\Models\Vehicle::categoryOptions()[$vehicle->categoria] ?? $vehicle->categoria }}</span>
                            </div>
                            <div class="mini-grid-wrap">
                                <table class="mini-grid">
                                    <thead>
                                        <tr>
                                            <th class="mini-time">Horario</th>
                                            @foreach ($weekDays as $day)
                                                <th>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('d/m') }}<br><span class="muted">{{ $day->format('d/m') }}</span></th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($timeSlots as $slot)
                                            <tr>
                                                <td class="mini-time">{{ $slot }}</td>
                                                @foreach ($weekDays as $day)
                                                    @php
                                                        $appointment = $vehicleSchedules[$vehicle->id][$slot][$day->toDateString()] ?? null;
                                                    @endphp
                                                    <td>
                                                        <div class="mini-cell {{ $appointment ? 'busy' : '' }}">
                                                            @if ($appointment)
                                                                <strong>{{ $appointment->teacher?->nome ?: 'Indisponivel' }}</strong>
                                                                @if ($appointment->student)
                                                                    <div>{{ $appointment->student->nome }}</div>
                                                                @endif
                                                            @else
                                                                <span class="mini-empty">Livre</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
@endsection
