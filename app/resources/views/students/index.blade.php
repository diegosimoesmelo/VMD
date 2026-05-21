@extends('layouts.panel', ['title' => 'Alunos cadastrados'])

@section('content')
    @php
        $currentTab = $filters['tab'] ?? 'active';
        $search = $filters['search'] ?? '';
        $teacherFilter = $filters['teacher_id'] ?? '';
        $timelineStatusFilter = $filters['timeline_status'] ?? '';
        $statusFlow = \App\Models\Student::statusFlow();
        $statusLabels = \App\Models\Student::statusOptions();
        $hasActiveFilters = $search !== '' || $teacherFilter !== '' || $timelineStatusFilter !== '';
        $activeCount = $tabCounts['active'] ?? 0;
        $withoutTeacherCount = $tabCounts['without_teacher'] ?? 0;
        $finishedCount = $tabCounts['finished'] ?? 0;
        $serviceLabels = [
            'primeira_habilitacao' => 'Primeira habilitação',
            'adicao_categoria' => 'Adição de categoria',
            'aula_habilitado' => 'Aula para habilitado',
            'prova_atualizacao' => 'Prova de Atualização',
            'prova_reciclagem' => 'Prova de Reciclagem',
        ];
    @endphp

    <style>
        .student-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .student-tab {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 16px;
            text-decoration: none;
            color: var(--color-secondary);
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            box-shadow: var(--shadow-card);
            font-weight: 700;
        }
        .student-tab.active {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.14), rgba(var(--color-secondary-rgb), 0.08));
            border-color: rgba(217, 119, 6, 0.28);
        }
        .student-tab-count {
            min-width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(var(--color-secondary-rgb), 0.08);
            font-size: 12px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
            gap: 14px;
            align-items: end;
        }
        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .record-table-wrap { overflow-x: auto; }
        .record-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1380px;
        }
        .record-table th,
        .record-table td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            vertical-align: top;
        }
        .record-table th {
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .record-table tbody tr:hover {
            background: rgba(217, 119, 6, 0.04);
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
        .teacher-badge,
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .teacher-badge {
            background: rgba(var(--color-secondary-rgb), 0.08);
            color: var(--color-secondary);
        }
        .teacher-badge.empty {
            background: rgba(239, 68, 68, 0.10);
            color: #991b1b;
        }
        .status-badge {
            background: rgba(217, 119, 6, 0.12);
            color: #9a6700;
        }
        .status-summary {
            display: grid;
            gap: 10px;
            min-width: 230px;
        }
        .status-timeline {
            display: grid;
            gap: 10px;
            min-width: 290px;
        }
        .status-step {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--color-muted-text);
            font-size: 13px;
        }
        .status-step-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: rgba(var(--color-secondary-rgb), 0.14);
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.9);
            flex-shrink: 0;
        }
        .status-step.done,
        .status-step.current {
            color: var(--color-secondary);
            font-weight: 600;
        }
        .status-step.done .status-step-dot {
            background: #16a34a;
        }
        .status-step.current .status-step-dot {
            background: var(--color-primary);
            box-shadow: 0 0 0 5px rgba(217, 119, 6, 0.14);
        }
        .status-step-line {
            width: 2px;
            height: 18px;
            margin-left: 5px;
            background: rgba(var(--color-secondary-rgb), 0.10);
        }
        .status-step.done + .status-step-line {
            background: rgba(22, 163, 74, 0.32);
        }
        .action-stack {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .inline-form {
            margin: 0;
        }
        .inline-form button {
            width: auto;
            margin: 0;
        }
        .data-muted {
            color: var(--color-muted-text);
            font-size: 13px;
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
            box-shadow: none;
        }
        .timeline-modal {
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
        .timeline-modal.is-open {
            display: flex;
        }
        .timeline-modal-card {
            width: min(560px, 100%);
            max-height: calc(100vh - 48px);
            display: flex;
            flex-direction: column;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            overflow: hidden;
        }
        .timeline-modal-card.wide {
            width: min(860px, 100%);
        }
        .timeline-modal-header,
        .timeline-modal-body,
        .timeline-modal-footer {
            padding: 22px 24px;
        }
        .timeline-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            flex-shrink: 0;
        }
        .timeline-modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }
        .timeline-modal-footer {
            flex-shrink: 0;
        }
        .timeline-modal-title {
            margin: 0 0 6px;
            font-size: 22px;
            color: var(--color-secondary);
        }
        .modal-tabs {
            display: flex;
            gap: 8px;
            padding: 0 0 18px;
            border-bottom: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            margin-bottom: 18px;
        }
        .modal-tab {
            width: auto;
            margin: 0;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(var(--color-secondary-rgb), 0.1);
            background: rgba(255, 255, 255, 0.92);
            color: var(--color-secondary);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: none;
        }
        .modal-tab.active {
            background: rgba(217, 119, 6, 0.12);
            border-color: rgba(217, 119, 6, 0.28);
            color: #9a6700;
        }
        .modal-tab-panel {
            display: none;
        }
        .modal-tab-panel.active {
            display: block;
        }
        .schedule-list {
            display: grid;
            gap: 12px;
        }
        .lesson-balance-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .lesson-export-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
            padding: 16px;
            margin-bottom: 18px;
            border-radius: 18px;
            background: rgba(var(--color-secondary-rgb), 0.04);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .lesson-export-form label {
            display: block;
            margin-bottom: 7px;
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .profile-export-card {
            display: grid;
            gap: 12px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(var(--color-secondary-rgb), 0.04);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .profile-export-card h3 {
            margin: 0;
            color: var(--color-secondary);
            font-size: 18px;
        }
        .profile-export-card p {
            margin: 0;
            color: var(--color-muted-text);
            line-height: 1.5;
        }
        .profile-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 6px;
        }
        .profile-summary-item {
            padding: 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .profile-summary-item span {
            display: block;
            margin-bottom: 4px;
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
        }
        .profile-summary-item strong {
            color: var(--color-secondary);
        }
        .lesson-balance-card {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(217, 119, 6, 0.08);
            border: 1px solid rgba(217, 119, 6, 0.16);
        }
        .lesson-balance-card strong {
            display: block;
            color: var(--color-secondary);
            font-size: 22px;
            margin-bottom: 4px;
        }
        .lesson-balance-card span {
            color: var(--color-muted-text);
            font-size: 13px;
            font-weight: 600;
        }
        .purchase-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            padding: 16px;
            margin-bottom: 18px;
            border-radius: 18px;
            background: rgba(var(--color-secondary-rgb), 0.04);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .purchase-form h3,
        .purchase-history h3 {
            grid-column: 1 / -1;
            margin: 0;
            color: var(--color-secondary);
            font-size: 16px;
        }
        .purchase-form label {
            display: block;
            margin-bottom: 7px;
            color: var(--color-muted-text);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .purchase-form textarea {
            min-height: 78px;
            resize: vertical;
        }
        .purchase-form .full-row {
            grid-column: 1 / -1;
        }
        .purchase-form .purchase-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
        }
        .purchase-history {
            display: grid;
            gap: 12px;
            margin-bottom: 18px;
        }
        .purchase-item {
            display: grid;
            gap: 6px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(22, 163, 74, 0.07);
            border: 1px solid rgba(22, 163, 74, 0.14);
        }
        .purchase-item strong {
            color: var(--color-secondary);
        }
        .purchase-item span,
        .purchase-item div {
            color: var(--color-muted-text);
            font-size: 13px;
        }
        .sweet-confirm {
            position: fixed;
            inset: 0;
            z-index: 120;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(6px);
        }
        .sweet-confirm.is-open {
            display: flex;
        }
        .sweet-confirm-card {
            width: min(420px, 100%);
            padding: 26px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            box-shadow: var(--shadow-soft);
            text-align: center;
        }
        .sweet-confirm-icon {
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border-radius: 999px;
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            font-size: 28px;
            font-weight: 800;
        }
        .sweet-confirm-title {
            margin: 0 0 8px;
            color: var(--color-secondary);
            font-size: 20px;
        }
        .sweet-confirm-text {
            margin: 0 0 20px;
            color: var(--color-muted-text);
            line-height: 1.5;
        }
        .sweet-confirm-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .sweet-toast {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 130;
            display: none;
            max-width: 340px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #166534;
            color: #fff;
            box-shadow: var(--shadow-soft);
            font-weight: 700;
        }
        .sweet-toast.is-open {
            display: block;
        }
        .sweet-toast.error {
            background: #991b1b;
        }
        .schedule-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(var(--color-secondary-rgb), 0.04);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .schedule-item strong {
            display: block;
            margin-bottom: 6px;
            color: var(--color-secondary);
        }
        .schedule-actions {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
        }
        .schedule-cancel-button {
            width: auto;
            margin: 0;
            padding: 9px 12px;
            border-radius: 12px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            background: rgba(239, 68, 68, 0.08);
            color: #991b1b;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: none;
        }
        .schedule-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .schedule-status.scheduled {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }
        .schedule-status.completed {
            background: rgba(34, 197, 94, 0.12);
            color: #166534;
        }
        .schedule-status.student-absent {
            background: rgba(249, 115, 22, 0.14);
            color: #9a3412;
        }
        .schedule-status.vehicle-issue {
            background: rgba(239, 68, 68, 0.12);
            color: #991b1b;
        }
        .schedule-empty {
            padding: 18px;
            border-radius: 18px;
            background: rgba(var(--color-secondary-rgb), 0.04);
            color: var(--color-muted-text);
        }
        .timeline-close {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 0;
            background: rgba(var(--color-secondary-rgb), 0.08);
            color: var(--color-secondary);
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
        }
        .timeline-modal-footer {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        @media (max-width: 1100px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            .lesson-balance-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .lesson-export-form {
                grid-template-columns: 1fr;
            }
            .profile-summary-grid {
                grid-template-columns: 1fr;
            }
            .purchase-form {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Módulo de alunos</span>
            <h1>Linha do tempo operacional por aluno</h1>
            <p>Visualize em que etapa cada aluno está e avance o status diretamente da listagem para manter o fluxo da autoescola atualizado.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $students->count() }}</strong>
                    <span>alunos na aba</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $students->where('status', \App\Models\Student::STATUS_PRACTICAL_CLASS)->count() }}</strong>
                    <span>em aula prática</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $students->where('status', \App\Models\Student::STATUS_THEORY_CLASS)->count() }}</strong>
                    <span>em aula teórica</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn" href="{{ route('students.create') }}">Cadastrar aluno</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div class="notice notice-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="student-tabs">
        <a
            class="student-tab {{ $currentTab === 'active' ? 'active' : '' }}"
            href="{{ route('students.index', array_filter(['tab' => 'active', 'search' => $search, 'teacher_id' => $teacherFilter, 'timeline_status' => $timelineStatusFilter], fn ($value) => $value !== '')) }}"
        >
            Alunos ativos
            <span class="student-tab-count">{{ $currentTab === 'active' ? $students->count() : $activeCount }}</span>
        </a>
        <a
            class="student-tab {{ $currentTab === 'without_teacher' ? 'active' : '' }}"
            href="{{ route('students.index', array_filter(['tab' => 'without_teacher', 'search' => $search, 'teacher_id' => $teacherFilter, 'timeline_status' => $timelineStatusFilter], fn ($value) => $value !== '')) }}"
        >
            Sem professor
            <span class="student-tab-count">{{ $currentTab === 'without_teacher' ? $students->count() : $withoutTeacherCount }}</span>
        </a>
        <a
            class="student-tab {{ $currentTab === 'finished' ? 'active' : '' }}"
            href="{{ route('students.index', array_filter(['tab' => 'finished', 'search' => $search, 'teacher_id' => $teacherFilter, 'timeline_status' => $timelineStatusFilter], fn ($value) => $value !== '')) }}"
        >
            Finalizados
            <span class="student-tab-count">{{ $currentTab === 'finished' ? $students->count() : $finishedCount }}</span>
        </a>
    </div>

    <div class="surface-card section-card">
        <form method="GET" action="{{ route('students.index') }}">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <div class="filter-grid">
                <div>
                    <label for="search">Buscar por nome ou CPF</label>
                    <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Digite o nome completo ou CPF do aluno">
                </div>
                <div>
                    <label for="teacher_id">Professor vinculado</label>
                    <select id="teacher_id" name="teacher_id">
                        <option value="">Todos</option>
                        <option value="without_teacher" @selected($teacherFilter === 'without_teacher')>Sem professor</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) $teacher->id === $teacherFilter)>{{ $teacher->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="timeline_status">Etapa da linha do tempo</label>
                    <select id="timeline_status" name="timeline_status">
                        <option value="">Todas</option>
                        @foreach ($statusLabels as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected($timelineStatusFilter === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn" type="submit">Filtrar</button>
                </div>
                <div class="filter-actions">
                    @if ($hasActiveFilters)
                        <a class="btn-secondary" href="{{ route('students.index', ['tab' => $currentTab]) }}">Limpar</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if ($students->isEmpty())
        <div class="surface-card empty-state">
            <strong>Nenhum aluno encontrado nesta aba.</strong>
            <p>
                @if ($hasActiveFilters)
                    Ajuste os filtros para encontrar outro aluno ou limpe a busca atual.
                @else
                    Cadastre um novo aluno para iniciar o acompanhamento por etapas.
                @endif
            </p>
            <a class="btn" href="{{ route('students.create') }}">Cadastrar aluno</a>
        </div>
    @else
        <div class="surface-card table-card">
            <div class="record-table-wrap">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Aluno</th>
                            <th>Professor</th>
                            <th>Estado atual</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Categoria</th>
                            <th>Serviço</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php
                                $currentIndex = array_search($student->status, $statusFlow, true);
                                $currentIndex = $currentIndex === false ? -1 : $currentIndex;
                            @endphp
                            <tr>
                                <td>
                                    <span class="record-title">{{ $student->matricula ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="record-title">{{ $student->nome }}</span>
                                    <span class="record-subtitle">{{ $student->email ?: 'Sem email cadastrado' }}</span>
                                </td>
                                <td>
                                    @if ($student->teacher)
                                        <span class="teacher-badge">{{ $student->teacher->nome }}</span>
                                    @else
                                        <span class="teacher-badge empty">Sem professor</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="status-summary">
                                        <span class="status-badge">{{ $student->statusLabel() }}</span>
                                        <button class="modal-trigger" type="button" data-modal-target="timeline-modal-{{ $student->id }}">
                                            Ver linha do tempo
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $student->cpf }}</td>
                                <td>{{ $student->telefone }}</td>
                                <td>
                                    <span class="record-title">{{ $student->categoria_pretendida ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="record-title">{{ $serviceLabels[$student->servico_oferecido] ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a class="btn-secondary" href="{{ route('students.edit', $student) }}">Editar</a>
                                        <button class="modal-trigger" type="button" data-modal-target="appointments-modal-{{ $student->id }}">
                                            Detalhes
                                        </button>
                                        @if ($student->nextStatus())
                                            <form class="inline-form" method="POST" action="{{ route('students.advance-status', $student) }}">
                                                @csrf
                                                <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                <input type="hidden" name="search" value="{{ $search }}">
                                                <input type="hidden" name="teacher_id" value="{{ $teacherFilter }}">
                                                <input type="hidden" name="timeline_status" value="{{ $timelineStatusFilter }}">
                                                <button class="btn" type="submit">Avançar etapa</button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="timeline-modal" id="timeline-modal-{{ $student->id }}" aria-hidden="true">
                                        <div class="timeline-modal-card" role="dialog" aria-modal="true" aria-labelledby="timeline-modal-title-{{ $student->id }}">
                                            <div class="timeline-modal-header">
                                                <div>
                                                    <h2 class="timeline-modal-title" id="timeline-modal-title-{{ $student->id }}">{{ $student->nome }}</h2>
                                                    <p>Etapa atual: {{ $student->statusLabel() }}</p>
                                                </div>
                                                <button class="timeline-close" type="button" data-modal-close aria-label="Fechar">&times;</button>
                                            </div>
                                            <div class="timeline-modal-body">
                                                <div class="status-timeline">
                                                    @foreach ($statusFlow as $index => $status)
                                                        @php
                                                            $stepClass = 'pending';
                                                            if ($index < $currentIndex) {
                                                                $stepClass = 'done';
                                                            } elseif ($index === $currentIndex) {
                                                                $stepClass = 'current';
                                                            }
                                                        @endphp
                                                        <div class="status-step {{ $stepClass }}">
                                                            <span class="status-step-dot"></span>
                                                            <span>{{ $statusLabels[$status] }}</span>
                                                        </div>
                                                        @if (! $loop->last)
                                                            <div class="status-step-line"></div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="timeline-modal-footer">
                                                <button class="btn-secondary" type="button" data-modal-close>Fechar</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="timeline-modal" id="appointments-modal-{{ $student->id }}" aria-hidden="true">
                                        <div class="timeline-modal-card wide" role="dialog" aria-modal="true" aria-labelledby="appointments-modal-title-{{ $student->id }}">
                                            <div class="timeline-modal-header">
                                                <div>
                                                    <h2 class="timeline-modal-title" id="appointments-modal-title-{{ $student->id }}">{{ $student->nome }}</h2>
                                                    <p>Aulas, compras e recibos vinculados a este aluno.</p>
                                                </div>
                                                <button class="timeline-close" type="button" data-modal-close aria-label="Fechar">&times;</button>
                                            </div>
                                            <div class="timeline-modal-body">
                                                <div class="modal-tabs" role="tablist" aria-label="Detalhes de {{ $student->nome }}">
                                                    <button class="modal-tab active" type="button" role="tab" aria-selected="true" data-tab-target="lessons-tab-{{ $student->id }}">Aulas</button>
                                                    <button class="modal-tab" type="button" role="tab" aria-selected="false" data-tab-target="purchases-tab-{{ $student->id }}">Compras</button>
                                                    <button class="modal-tab" type="button" role="tab" aria-selected="false" data-tab-target="receipts-tab-{{ $student->id }}">Recibos</button>
                                                    <button class="modal-tab" type="button" role="tab" aria-selected="false" data-tab-target="profile-tab-{{ $student->id }}">Ficha</button>
                                                </div>

                                                <div class="modal-tab-panel active" id="lessons-tab-{{ $student->id }}" role="tabpanel">
                                                    @php
                                                        $exportLessonCategories = match ($student->categoria_pretendida) {
                                                            'A' => ['A' => 'Categoria A'],
                                                            'B' => ['B' => 'Categoria B'],
                                                            'AB' => ['AB' => 'Categorias A e B', 'A' => 'Somente categoria A', 'B' => 'Somente categoria B'],
                                                            default => [],
                                                        };
                                                    @endphp
                                                    <form class="lesson-export-form" method="GET" action="{{ route('students.lessons.pdf', $student) }}" target="_blank" rel="noopener">
                                                        <div>
                                                            <label for="lesson_export_category_{{ $student->id }}">Exportar aulas</label>
                                                            <select id="lesson_export_category_{{ $student->id }}" name="category" required>
                                                                @forelse ($exportLessonCategories as $categoryValue => $categoryLabel)
                                                                    <option value="{{ $categoryValue }}">{{ $categoryLabel }}</option>
                                                                @empty
                                                                    <option value="">Categoria do aluno não informada</option>
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                        <button class="btn" type="submit" @disabled(empty($exportLessonCategories))>Exportar PDF</button>
                                                    </form>

                                                    <div class="lesson-balance-grid">
                                                        <div class="lesson-balance-card">
                                                            <strong data-balance-field="a_contracted">{{ $student->quantidade_aulas_a_contratadas ?? 0 }}</strong>
                                                            <span>Aulas A contratadas</span>
                                                        </div>
                                                        <div class="lesson-balance-card">
                                                            <strong data-balance-field="a_remaining">{{ $student->quantidade_aulas_a_restantes ?? ($student->quantidade_aulas_a_contratadas ?? 0) }}</strong>
                                                            <span>Aulas A restantes</span>
                                                        </div>
                                                        <div class="lesson-balance-card">
                                                            <strong data-balance-field="b_contracted">{{ $student->quantidade_aulas_b_contratadas ?? 0 }}</strong>
                                                            <span>Aulas B contratadas</span>
                                                        </div>
                                                        <div class="lesson-balance-card">
                                                            <strong data-balance-field="b_remaining">{{ $student->quantidade_aulas_b_restantes ?? ($student->quantidade_aulas_b_contratadas ?? 0) }}</strong>
                                                            <span>Aulas B restantes</span>
                                                        </div>
                                                    </div>

                                                    @if ($student->appointments->isEmpty())
                                                        <div class="schedule-empty">Nenhuma aula marcada para este aluno até o momento.</div>
                                                    @else
                                                        <div class="schedule-list">
                                                            @foreach ($student->appointments as $appointment)
                                                                @php
                                                                    $lessonStatus = $appointment->effectiveLessonStatus();
                                                                    $lessonStatusClass = match ($lessonStatus) {
                                                                        \App\Models\Appointment::LESSON_STATUS_COMPLETED => 'completed',
                                                                        \App\Models\Appointment::LESSON_STATUS_STUDENT_ABSENT => 'student-absent',
                                                                        \App\Models\Appointment::LESSON_STATUS_VEHICLE_ISSUE => 'vehicle-issue',
                                                                        default => 'scheduled',
                                                                    };
                                                                @endphp
                                                            <div class="schedule-item" data-appointment-item="{{ $appointment->id }}">
                                                                    <strong>{{ $appointment->starts_at?->format('d/m/Y') }} as {{ $appointment->starts_at?->format('H:i') }}</strong>
                                                                    <span class="schedule-status {{ $lessonStatusClass }}">{{ $appointment->effectiveLessonStatusLabel() }}</span>
                                                                    <div>Professor: {{ $appointment->teacher?->nome ?: '-' }}</div>
                                                                    <div>Veículo: {{ $appointment->vehicle ? strtoupper($appointment->vehicle->placa) : '-' }}</div>
                                                                    @if ($appointment->lesson_category)
                                                                        <div>Categoria da aula: {{ $appointment->lesson_category }}</div>
                                                                    @endif
                                                                    @if ($appointment->lesson_status_notes)
                                                                        <div>Status operacional: {{ $appointment->lesson_status_notes }}</div>
                                                                    @endif
                                                                @if ($appointment->notes)
                                                                    <div>Observações: {{ $appointment->notes }}</div>
                                                                @endif
                                                                @if ($appointment->starts_at?->isFuture())
                                                                    <div class="schedule-actions">
                                                                        <form class="inline-form cancel-appointment-form" method="POST" action="{{ route('appointments.destroy', $appointment) }}">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <input type="hidden" name="return_to_students" value="1">
                                                                            <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                                            <input type="hidden" name="search" value="{{ $search }}">
                                                                            <input type="hidden" name="teacher_id" value="{{ $teacherFilter }}">
                                                                            <input type="hidden" name="timeline_status" value="{{ $timelineStatusFilter }}">
                                                                            <button class="schedule-cancel-button" type="submit">Cancelar agendamento</button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="modal-tab-panel" id="purchases-tab-{{ $student->id }}" role="tabpanel">
                                                    @php
                                                        $purchaseLessonCategories = match ($student->categoria_pretendida) {
                                                            'A' => ['A'],
                                                            'B' => ['B'],
                                                            'AB' => ['A', 'B'],
                                                            default => [],
                                                        };
                                                    @endphp
                                                    <form class="purchase-form receipt-target-form" method="POST" action="{{ route('students.lesson-purchases.store', $student) }}" data-receipt-amount-field="amount_paid">
                                                        @csrf
                                                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                        <input type="hidden" name="search" value="{{ $search }}">
                                                        <input type="hidden" name="teacher_id" value="{{ $teacherFilter }}">
                                                        <input type="hidden" name="timeline_status" value="{{ $timelineStatusFilter }}">

                                                        <h3>Registrar compra de aulas</h3>
                                                        <div>
                                                            <label for="lesson_category_{{ $student->id }}">Categoria</label>
                                                            <select id="lesson_category_{{ $student->id }}" name="lesson_category" required>
                                                                @forelse ($purchaseLessonCategories as $lessonCategory)
                                                                    <option value="{{ $lessonCategory }}">Aulas {{ $lessonCategory }}</option>
                                                                @empty
                                                                    <option value="">Categoria do aluno não informada</option>
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label for="quantity_{{ $student->id }}">Quantidade</label>
                                                            <input id="quantity_{{ $student->id }}" name="quantity" type="number" min="1" step="1" required>
                                                        </div>
                                                        <div>
                                                            <label for="amount_paid_{{ $student->id }}">Valor pago</label>
                                                            <input id="amount_paid_{{ $student->id }}" name="amount_paid" type="number" min="0" step="0.01" placeholder="Opcional">
                                                        </div>
                                                        <div>
                                                            <label for="purchase_payment_method_{{ $student->id }}">Tipo de pagamento</label>
                                                            <select id="purchase_payment_method_{{ $student->id }}" name="payment_method">
                                                                <option value="">Selecione</option>
                                                                @foreach (config('receipt.payment_methods') as $methodValue => $methodLabel)
                                                                    <option value="{{ $methodValue }}">{{ $methodLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="full-row">
                                                            <label for="notes_{{ $student->id }}">Observações</label>
                                                            <textarea id="notes_{{ $student->id }}" name="notes" placeholder="Opcional"></textarea>
                                                        </div>
                                                        <div class="purchase-actions">
                                                            <button class="btn" type="submit">Adicionar aulas</button>
                                                        </div>
                                                    </form>

                                                    <div class="purchase-history">
                                                        <h3>Histórico de compras</h3>
                                                        @if ($student->lessonPurchases->isEmpty())
                                                            <div class="schedule-empty">Nenhuma compra adicional registrada para este aluno.</div>
                                                        @else
                                                            @foreach ($student->lessonPurchases as $purchase)
                                                                <div class="purchase-item">
                                                                    <strong>{{ $purchase->quantity }} aula{{ $purchase->quantity === 1 ? '' : 's' }} {{ $purchase->lesson_category }}</strong>
                                                                    <span>
                                                                        {{ $purchase->purchased_at?->format('d/m/Y H:i') }}
                                                                        @if ($purchase->amount_paid !== null)
                                                                            - R$ {{ number_format((float) $purchase->amount_paid, 2, ',', '.') }}
                                                                        @endif
                                                                        @if ($purchase->user)
                                                                            - {{ $purchase->user->name ?: $purchase->user->username }}
                                                                        @endif
                                                                    </span>
                                                                    @if ($purchase->notes)
                                                                        <div>{{ $purchase->notes }}</div>
                                                                    @endif
                                                                    @if ($purchase->amount_paid !== null)
                                                                        <div>Recibo disponível na aba Recibos.</div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="modal-tab-panel" id="receipts-tab-{{ $student->id }}" role="tabpanel">
                                                    <div class="purchase-history">
                                                        <h3>Recibos</h3>
                                                        @if (($student->valor_pago ?? 0) > 0)
                                                            <div class="purchase-item">
                                                                <strong>Recibo do cadastro</strong>
                                                                <span>
                                                                    {{ $student->created_at?->format('d/m/Y H:i') }}
                                                                    - R$ {{ number_format((float) $student->valor_pago, 2, ',', '.') }}
                                                                </span>
                                                                <div>
                                                                    <a class="modal-trigger" href="{{ route('students.receipts.registration.show', $student) }}" target="_blank" rel="noopener">Abrir recibo</a>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @foreach ($student->lessonPurchases->whereNotNull('amount_paid') as $purchase)
                                                            <div class="purchase-item">
                                                                <strong>Compra de {{ $purchase->quantity }} aula{{ $purchase->quantity === 1 ? '' : 's' }} {{ $purchase->lesson_category }}</strong>
                                                                <span>
                                                                    {{ $purchase->purchased_at?->format('d/m/Y H:i') }}
                                                                    - R$ {{ number_format((float) $purchase->amount_paid, 2, ',', '.') }}
                                                                </span>
                                                                <div>
                                                                    <a class="modal-trigger" href="{{ route('lesson-purchases.receipts.show', $purchase) }}" target="_blank" rel="noopener">Abrir recibo</a>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                        @if (($student->valor_pago ?? 0) <= 0 && $student->lessonPurchases->whereNotNull('amount_paid')->isEmpty())
                                                            <div class="schedule-empty">Nenhum recibo disponível para este aluno.</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="modal-tab-panel" id="profile-tab-{{ $student->id }}" role="tabpanel">
                                                    <div class="profile-export-card">
                                                        <h3>Ficha cadastral</h3>
                                                        <p>Exporte um PDF com os principais dados de cadastro, endereço, contato, filiação, serviço contratado, aulas e observações do aluno.</p>
                                                        <div class="profile-summary-grid">
                                                            <div class="profile-summary-item">
                                                                <span>Matrícula</span>
                                                                <strong>{{ $student->matricula ?: '-' }}</strong>
                                                            </div>
                                                            <div class="profile-summary-item">
                                                                <span>CPF</span>
                                                                <strong>{{ $student->cpf ?: '-' }}</strong>
                                                            </div>
                                                            <div class="profile-summary-item">
                                                                <span>Categoria</span>
                                                                <strong>{{ $student->categoria_pretendida ?: '-' }}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="purchase-actions">
                                                            <a class="btn" href="{{ route('students.profile.pdf', $student) }}" target="_blank" rel="noopener">Exportar ficha PDF</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="timeline-modal-footer">
                                                <button class="btn-secondary" type="button" data-modal-close>Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="sweet-confirm" id="cancelAppointmentConfirm" aria-hidden="true">
        <div class="sweet-confirm-card" role="dialog" aria-modal="true" aria-labelledby="cancelAppointmentConfirmTitle">
            <div class="sweet-confirm-icon">!</div>
            <h2 class="sweet-confirm-title" id="cancelAppointmentConfirmTitle">Cancelar agendamento?</h2>
            <p class="sweet-confirm-text">Essa aula futura será removida da agenda e o saldo do aluno será atualizado.</p>
            <div class="sweet-confirm-actions">
                <button class="btn-secondary" type="button" data-sweet-cancel>Voltar</button>
                <button class="schedule-cancel-button" type="button" data-sweet-confirm>Sim, cancelar</button>
            </div>
        </div>
    </div>
    <div class="sweet-toast" id="sweetToast" role="status" aria-live="polite"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var pendingCancelForm = null;
            var confirmDialog = document.getElementById('cancelAppointmentConfirm');
            var toast = document.getElementById('sweetToast');

            function openCancelConfirm(form) {
                pendingCancelForm = form;

                if (confirmDialog) {
                    confirmDialog.classList.add('is-open');
                    confirmDialog.setAttribute('aria-hidden', 'false');
                }
            }

            function closeCancelConfirm() {
                pendingCancelForm = null;

                if (confirmDialog) {
                    confirmDialog.classList.remove('is-open');
                    confirmDialog.setAttribute('aria-hidden', 'true');
                }
            }

            function showToast(message, type) {
                if (! toast) {
                    return;
                }

                toast.textContent = message;
                toast.classList.toggle('error', type === 'error');
                toast.classList.add('is-open');

                window.setTimeout(function () {
                    toast.classList.remove('is-open');
                }, 3200);
            }

            function updateStudentBalance(form, balance) {
                if (! balance) {
                    return;
                }

                var modalBody = form.closest('.timeline-modal-body');

                if (! modalBody) {
                    return;
                }

                Object.keys(balance).forEach(function (key) {
                    var target = modalBody.querySelector('[data-balance-field="' + key + '"]');

                    if (target) {
                        target.textContent = balance[key];
                    }
                });
            }

            function removeAppointmentItem(form) {
                var item = form.closest('.schedule-item');
                var list = form.closest('.schedule-list');

                if (item) {
                    item.remove();
                }

                if (list && ! list.querySelector('.schedule-item')) {
                    var empty = document.createElement('div');
                    empty.className = 'schedule-empty';
                    empty.textContent = 'Nenhuma aula marcada para este aluno até o momento.';
                    list.replaceWith(empty);
                }
            }

            function submitCancelForm(form) {
                var formData = new FormData(form);

                form.querySelectorAll('button').forEach(function (button) {
                    button.disabled = true;
                });

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (response) {
                        if (! response.ok) {
                            throw new Error('Nao foi possivel cancelar o agendamento.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        updateStudentBalance(form, data.student_balance);
                        removeAppointmentItem(form);
                        showToast(data.message || 'Agendamento cancelado com sucesso.');
                    })
                    .catch(function () {
                        showToast('Nao foi possivel cancelar o agendamento. Tente novamente.', 'error');
                    })
                    .finally(function () {
                        form.querySelectorAll('button').forEach(function (button) {
                            button.disabled = false;
                        });
                    });
            }

            document.querySelectorAll('.cancel-appointment-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openCancelConfirm(form);
                });
            });

            document.querySelectorAll('[data-sweet-cancel]').forEach(function (button) {
                button.addEventListener('click', closeCancelConfirm);
            });

            document.querySelectorAll('[data-sweet-confirm]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var form = pendingCancelForm;

                    closeCancelConfirm();

                    if (form) {
                        submitCancelForm(form);
                    }
                });
            });

            if (confirmDialog) {
                confirmDialog.addEventListener('click', function (event) {
                    if (event.target === confirmDialog) {
                        closeCancelConfirm();
                    }
                });
            }

            document.querySelectorAll('[data-modal-target]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var modal = document.getElementById(button.getAttribute('data-modal-target'));

                    if (modal) {
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            document.querySelectorAll('.timeline-modal').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal || event.target.hasAttribute('data-modal-close')) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            document.querySelectorAll('.modal-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var modalBody = tab.closest('.timeline-modal-body');
                    var target = document.getElementById(tab.getAttribute('data-tab-target'));

                    if (! modalBody || ! target) {
                        return;
                    }

                    modalBody.querySelectorAll('.modal-tab').forEach(function (item) {
                        item.classList.remove('active');
                        item.setAttribute('aria-selected', 'false');
                    });

                    modalBody.querySelectorAll('.modal-tab-panel').forEach(function (panel) {
                        panel.classList.remove('active');
                    });

                    tab.classList.add('active');
                    tab.setAttribute('aria-selected', 'true');
                    target.classList.add('active');
                });
            });

            document.querySelectorAll('.receipt-target-form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var amountField = form.querySelector('[name="' + form.dataset.receiptAmountField + '"]');
                    var amount = amountField ? parseFloat(amountField.value || '0') : 0;

                    if (amount > 0) {
                        form.setAttribute('target', '_blank');
                    } else {
                        form.removeAttribute('target');
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCancelConfirm();
                    document.querySelectorAll('.timeline-modal.is-open').forEach(function (modal) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    });
                }
            });
        });
    </script>
@endsection

