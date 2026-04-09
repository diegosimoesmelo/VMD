@extends('layouts.panel', ['title' => 'Professores cadastrados'])

@section('content')
    @php
        $weekDayLabels = [
            1 => 'Seg',
            2 => 'Ter',
            3 => 'Qua',
            4 => 'Qui',
            5 => 'Sex',
            6 => 'Sáb',
        ];
    @endphp
    <style>
        .record-table-wrap { overflow-x: auto; }
        .record-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 900px;
        }
        .record-table th,
        .record-table td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            vertical-align: middle;
        }
        .record-table th {
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .record-table tbody tr:hover { background: rgba(217, 119, 6, 0.05); }
        .tag-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(var(--color-secondary-rgb), 0.06);
            color: var(--color-secondary);
            font-size: 12px;
            font-weight: 700;
        }
        .record-title {
            color: var(--color-secondary);
            font-weight: 700;
        }
        .record-subtitle {
            display: block;
            margin-top: 4px;
            color: var(--color-muted-text);
            font-size: 13px;
        }
        .tag.muted {
            background: rgba(239, 68, 68, 0.10);
            color: #991b1b;
        }
        .action-stack {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .modal-trigger {
            width: auto;
            margin: 0;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(var(--color-secondary-rgb), 0.1);
            background: rgba(255, 255, 255, 0.92);
            color: var(--color-secondary);
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .schedule-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(6px);
        }
        .schedule-modal.is-open {
            display: flex;
        }
        .schedule-modal-card {
            width: min(1180px, 100%);
            max-height: calc(100vh - 48px);
            display: flex;
            flex-direction: column;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            overflow: hidden;
        }
        .schedule-modal-header,
        .schedule-modal-body,
        .schedule-modal-footer {
            padding: 22px 24px;
        }
        .schedule-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            flex-shrink: 0;
        }
        .schedule-modal-title {
            margin: 0 0 6px;
            font-size: 22px;
            color: var(--color-secondary);
        }
        .schedule-modal-body {
            overflow: auto;
            flex: 1 1 auto;
        }
        .schedule-modal-footer {
            border-top: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            flex-shrink: 0;
        }
        .schedule-close {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 0;
            background: rgba(var(--color-secondary-rgb), 0.08);
            color: var(--color-secondary);
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }
        .schedule-grid-wrap {
            overflow-x: auto;
        }
        .schedule-grid {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .schedule-grid th,
        .schedule-grid td {
            padding: 12px;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            border-right: 1px solid rgba(var(--color-secondary-rgb), 0.05);
            vertical-align: top;
        }
        .schedule-grid th {
            background: rgba(var(--color-secondary-rgb), 0.04);
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 700;
        }
        .schedule-time {
            width: 96px;
            white-space: nowrap;
            font-weight: 700;
            color: var(--color-secondary);
        }
        .schedule-cell {
            min-height: 76px;
            border-radius: 16px;
            padding: 10px 12px;
            background: rgba(var(--color-secondary-rgb), 0.04);
        }
        .schedule-cell.busy {
            background: rgba(217, 119, 6, 0.08);
        }
        .schedule-cell strong {
            display: block;
            margin-bottom: 4px;
            color: var(--color-secondary);
        }
        .schedule-empty {
            color: var(--color-muted-text);
            font-size: 13px;
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Módulo de professores</span>
            <h1>Equipe organizada por categoria e disponibilidade</h1>
            <p>Visual mais elegante para gerenciar instrutores, turnos disponíveis e categorias ensinadas.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $teachers->count() }}</strong>
                    <span>professores ativos</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $weekStart->format('d/m') }}</strong>
                    <span>semana até {{ $weekStart->copy()->addDays(5)->format('d/m') }}</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn-secondary" href="{{ route('teachers.index', ['week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}">Semana anterior</a>
            <a class="btn-secondary" href="{{ route('teachers.index', ['week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}">Próxima semana</a>
            <a class="btn" href="{{ route('teachers.create') }}">Novo professor</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($teachers->isEmpty())
        <div class="surface-card empty-state">
            <strong>Nenhum professor cadastrado ainda.</strong>
            <p>Cadastre professores para alimentar a distribuição de aulas e os vínculos com os alunos.</p>
            <a class="btn" href="{{ route('teachers.create') }}">Cadastrar primeiro professor</a>
        </div>
    @else
        <div class="surface-card table-card">
            <div class="record-table-wrap">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Categorias</th>
                            <th>Turnos</th>
                            <th>Agenda</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td>
                                    <span class="record-title">{{ $teacher->nome }}</span>
                                    <span class="record-subtitle">Instrutor cadastrado</span>
                                </td>
                                <td>{{ $teacher->cpf }}</td>
                                <td>{{ $teacher->telefone }}</td>
                                <td>
                                    <div class="tag-list">
                                        @foreach ($teacher->categorias_ensino ?? [] as $category)
                                            <span class="tag">{{ $category }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="tag-list">
                                        @foreach ($teacher->turnos_disponiveis ?? [] as $shift)
                                            <span class="tag">{{ ucfirst($shift) }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="tag {{ $teacher->isSchedulable() ? '' : 'muted' }}">{{ $teacher->schedulingStatusLabel() }}</span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a class="btn-secondary" href="{{ route('teachers.edit', $teacher) }}">Editar</a>
                                        <button class="modal-trigger" type="button" data-modal-target="schedule-modal-{{ $teacher->id }}">Resumo semanal</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($teachers as $teacher)
            <div class="schedule-modal" id="schedule-modal-{{ $teacher->id }}" aria-hidden="true">
                <div class="schedule-modal-card" role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title-{{ $teacher->id }}">
                    <div class="schedule-modal-header">
                        <div>
                            <span class="eyebrow">Resumo semanal</span>
                            <h2 class="schedule-modal-title" id="schedule-modal-title-{{ $teacher->id }}">{{ $teacher->nome }}</h2>
                            <p>{{ $weekStart->format('d/m/Y') }} a {{ $weekStart->copy()->addDays(5)->format('d/m/Y') }}</p>
                        </div>
                        <button class="schedule-close" type="button" data-modal-close aria-label="Fechar">&times;</button>
                    </div>
                    <div class="schedule-modal-body">
                        <div class="schedule-grid-wrap">
                            <table class="schedule-grid">
                                <thead>
                                    <tr>
                                        <th class="schedule-time">Horário</th>
                                        @foreach ($weekDays as $day)
                                            <th>{{ $weekDayLabels[$day->dayOfWeekIso] ?? $day->format('d/m') }}<br><span class="muted">{{ $day->format('d/m') }}</span></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timeSlots as $slot)
                                        <tr>
                                            <td class="schedule-time">{{ $slot }}</td>
                                            @foreach ($weekDays as $day)
                                                @php
                                                    $appointment = $teacherSchedules[$teacher->id][$slot][$day->toDateString()] ?? null;
                                                @endphp
                                                <td>
                                                    <div class="schedule-cell {{ $appointment ? 'busy' : '' }}">
                                                        @if ($appointment)
                                                            <strong>{{ $appointment->student?->nome ?: 'Indisponível' }}</strong>
                                                            @if ($appointment->vehicle)
                                                                <div>Veículo: {{ strtoupper($appointment->vehicle->placa) }}</div>
                                                            @endif
                                                            @if ($appointment->lesson_category)
                                                                <div>Aula {{ $appointment->lesson_category }}</div>
                                                            @endif
                                                        @else
                                                            <span class="schedule-empty">Livre</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="schedule-modal-footer">
                        <button class="btn-secondary" type="button" data-modal-close>Fechar</button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-modal-target]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var modal = document.getElementById(button.getAttribute('data-modal-target'));

                    if (modal) {
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            document.querySelectorAll('.schedule-modal').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal || event.target.hasAttribute('data-modal-close')) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.schedule-modal.is-open').forEach(function (modal) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    });
                }
            });
        });
    </script>
@endsection

