@extends('layouts.panel', ['title' => 'Alunos cadastrados'])

@section('content')
    @php
        $currentTab = $filters['tab'] ?? 'active';
        $search = $filters['search'] ?? '';
        $teacherFilter = $filters['teacher_id'] ?? '';
        $statusFlow = \App\Models\Student::statusFlow();
        $statusLabels = \App\Models\Student::statusOptions();
        $hasActiveFilters = $search !== '' || $teacherFilter !== '';
        $activeCount = $tabCounts['active'] ?? 0;
        $withoutTeacherCount = $tabCounts['without_teacher'] ?? 0;
        $finishedCount = $tabCounts['finished'] ?? 0;
        $serviceLabels = [
            'primeira_habilitacao' => 'Primeira habilitacao',
            'adicao_categoria' => 'Adicao de categoria',
            'aula_habilitado' => 'Aula para habilitado',
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
            border: 1px solid rgba(20, 33, 61, 0.08);
            box-shadow: var(--shadow-card);
            font-weight: 700;
        }
        .student-tab.active {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.14), rgba(20, 33, 61, 0.08));
            border-color: rgba(217, 119, 6, 0.28);
        }
        .student-tab-count {
            min-width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(20, 33, 61, 0.08);
            font-size: 12px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
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
            border-bottom: 1px solid rgba(20, 33, 61, 0.08);
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
            background: rgba(20, 33, 61, 0.08);
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
            background: rgba(20, 33, 61, 0.14);
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
            background: rgba(20, 33, 61, 0.10);
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
            border: 1px solid rgba(20, 33, 61, 0.1);
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
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(20, 33, 61, 0.08);
            overflow: hidden;
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
            border-bottom: 1px solid rgba(20, 33, 61, 0.08);
        }
        .timeline-modal-title {
            margin: 0 0 6px;
            font-size: 22px;
            color: var(--color-secondary);
        }
        .timeline-close {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 0;
            background: rgba(20, 33, 61, 0.08);
            color: var(--color-secondary);
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
        }
        .timeline-modal-footer {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid rgba(20, 33, 61, 0.08);
        }
        @media (max-width: 1100px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Modulo de alunos</span>
            <h1>Linha do tempo operacional por aluno</h1>
            <p>Visualize em que etapa cada aluno esta e avance o status diretamente da listagem para manter o fluxo da autoescola atualizado.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $students->count() }}</strong>
                    <span>alunos na aba</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $students->where('status', \App\Models\Student::STATUS_PRACTICAL_CLASS)->count() }}</strong>
                    <span>em aula pratica</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $students->where('status', \App\Models\Student::STATUS_THEORY_CLASS)->count() }}</strong>
                    <span>em aula teorica</span>
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
            href="{{ route('students.index', array_filter(['tab' => 'active', 'search' => $search, 'teacher_id' => $teacherFilter], fn ($value) => $value !== '')) }}"
        >
            Alunos ativos
            <span class="student-tab-count">{{ $currentTab === 'active' ? $students->count() : $activeCount }}</span>
        </a>
        <a
            class="student-tab {{ $currentTab === 'without_teacher' ? 'active' : '' }}"
            href="{{ route('students.index', array_filter(['tab' => 'without_teacher', 'search' => $search, 'teacher_id' => $teacherFilter], fn ($value) => $value !== '')) }}"
        >
            Sem professor
            <span class="student-tab-count">{{ $currentTab === 'without_teacher' ? $students->count() : $withoutTeacherCount }}</span>
        </a>
        <a
            class="student-tab {{ $currentTab === 'finished' ? 'active' : '' }}"
            href="{{ route('students.index', array_filter(['tab' => 'finished', 'search' => $search, 'teacher_id' => $teacherFilter], fn ($value) => $value !== '')) }}"
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
                            <th>Matricula</th>
                            <th>Aluno</th>
                            <th>Professor</th>
                            <th>Estado atual</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Categoria</th>
                            <th>Servico</th>
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
                                        @if ($student->nextStatus())
                                            <form class="inline-form" method="POST" action="{{ route('students.advance-status', $student) }}">
                                                @csrf
                                                <input type="hidden" name="tab" value="{{ $currentTab }}">
                                                <input type="hidden" name="search" value="{{ $search }}">
                                                <input type="hidden" name="teacher_id" value="{{ $teacherFilter }}">
                                                <button class="btn" type="submit">Avancar etapa</button>
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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

            document.querySelectorAll('.timeline-modal').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal || event.target.hasAttribute('data-modal-close')) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.timeline-modal.is-open').forEach(function (modal) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    });
                }
            });
        });
    </script>
@endsection
