@extends('layouts.panel', ['title' => 'Usuarios administrativos'])

@section('content')
    <style>
        .record-table-wrap { overflow-x: auto; }
        .record-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 860px;
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
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Controle de acesso</span>
            <h1>Usuarios administrativos do sistema</h1>
            <p>Gerencie os acessos internos do painel e mantenha os perfis administrativos separados por responsabilidade.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $users->count() }}</strong>
                    <span>usuarios cadastrados</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn" href="{{ route('users.create') }}">Novo usuario</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($users->isEmpty())
        <div class="surface-card empty-state">
            <strong>Nenhum usuario administrativo cadastrado ainda.</strong>
            <p>Cadastre o primeiro usuario para distribuir os acessos entre gerente e administrativo.</p>
            <a class="btn" href="{{ route('users.create') }}">Cadastrar usuario</a>
        </div>
    @else
        <div class="surface-card table-card">
            <div class="record-table-wrap">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuario</th>
                            <th>Perfil</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <span class="record-title">{{ $user->name ?: $user->username }}</span>
                                    <span class="record-subtitle">Usuario interno do painel</span>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>
                                    <span class="tag">{{ $user->roleLabel() }}</span>
                                </td>
                                <td>
                                    <a class="btn-secondary" href="{{ route('users.edit', $user) }}">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
