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

        <div class="surface-card section-card">
            <h2>Operacao do dia</h2>
            <p>Use o menu lateral para acessar cadastros, agendamentos e o controle operacional das aulas.</p>
        </div>
    @endif
@endsection
