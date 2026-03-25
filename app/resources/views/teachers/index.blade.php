@extends('layouts.panel', ['title' => 'Professores cadastrados'])

@section('content')
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
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Modulo de professores</span>
            <h1>Equipe organizada por categoria e disponibilidade</h1>
            <p>Visual mais elegante para gerenciar instrutores, turnos disponiveis e categorias ensinadas.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $teachers->count() }}</strong>
                    <span>professores ativos</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn" href="{{ route('teachers.create') }}">Novo professor</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($teachers->isEmpty())
        <div class="surface-card empty-state">
            <strong>Nenhum professor cadastrado ainda.</strong>
            <p>Cadastre professores para alimentar a distribuicao de aulas e os vinculos com os alunos.</p>
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
                                    <a class="btn-secondary" href="{{ route('teachers.edit', $teacher) }}">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
