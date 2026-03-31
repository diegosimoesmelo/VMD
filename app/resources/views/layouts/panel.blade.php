<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Painel' }}</title>
    @include('partials.theme-style')
    <style>
        * { box-sizing: border-box; }
        :root {
            --shadow-soft: 0 24px 60px rgba(var(--color-secondary-rgb), 0.10);
            --shadow-card: 0 18px 40px rgba(var(--color-secondary-rgb), 0.08);
            --radius-xl: 28px;
        }
        body {
            margin: 0;
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            color: var(--color-text);
            background: var(--color-background);
            background-image:
                radial-gradient(circle at top left, rgba(217, 119, 6, 0.15), transparent 32%),
                radial-gradient(circle at bottom right, rgba(var(--color-secondary-rgb), 0.12), transparent 30%);
        }
        .app {
            min-height: 100vh;
            display: flex;
            gap: 24px;
            padding: 20px;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, rgba(var(--color-secondary-rgb), 0.98), rgba(var(--color-secondary-rgb), 0.88));
            color: #fff;
            padding: 24px 18px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 20px;
            height: calc(100vh - 40px);
            overflow: auto;
        }
        .brand {
            margin: 0;
            font-size: 28px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .brand-subtitle {
            margin: 8px 0 28px;
            color: rgba(255, 255, 255, 0.66);
            font-size: 13px;
            line-height: 1.55;
        }
        .menu-link {
            display: block;
            color: rgba(255, 255, 255, 0.78);
            text-decoration: none;
            border-radius: 14px;
            padding: 13px 14px;
            margin-bottom: 10px;
            transition: 0.22s ease;
        }
        .menu-link:hover,
        .menu-link.active {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.95), rgba(245, 158, 11, 0.82));
            color: #fff;
            transform: translateX(2px);
        }
        .logout-btn {
            margin-top: 22px;
            width: 100%;
            border: 0;
            border-radius: 14px;
            background: rgba(239, 68, 68, 0.14);
            color: #fff;
            padding: 13px 14px;
            cursor: pointer;
        }
        .content {
            flex: 1;
            padding: 4px 0;
        }
        .panel {
            min-height: calc(100vh - 48px);
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(18px);
            border-radius: 34px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-soft);
        }
        h1 {
            margin: 0;
            font-size: var(--font-size-title);
            color: var(--color-secondary);
            letter-spacing: -0.03em;
        }
        h2 {
            margin: 0 0 14px;
            font-size: 18px;
            color: var(--color-secondary);
        }
        p {
            margin: 0 0 10px;
            color: var(--color-muted-text);
            line-height: 1.6;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 600;
        }
        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid rgba(107, 114, 128, 0.18);
            border-radius: 14px;
            padding: 13px 14px;
            margin-bottom: 12px;
            font: inherit;
            background: rgba(255, 255, 255, 0.94);
            color: var(--color-text);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(217, 119, 6, 0.65);
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 0 16px;
        }
        .field { grid-column: span 12; }
        .col-2 { grid-column: span 2; }
        .col-3 { grid-column: span 3; }
        .col-4 { grid-column: span 4; }
        .col-5 { grid-column: span 5; }
        .col-6 { grid-column: span 6; }
        .col-7 { grid-column: span 7; }
        .col-8 { grid-column: span 8; }
        .col-9 { grid-column: span 9; }
        .col-10 { grid-column: span 10; }
        .col-12 { grid-column: span 12; }
        .btn {
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--color-primary), #f59e0b);
            color: #fff;
            padding: 12px 18px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 14px 28px rgba(217, 119, 6, 0.18);
        }
        .btn-secondary,
        .btn-ghost {
            border-radius: 14px;
            padding: 12px 18px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-secondary {
            border: 1px solid rgba(var(--color-secondary-rgb), 0.12);
            background: rgba(255, 255, 255, 0.88);
            color: var(--color-secondary);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 26px;
            padding: 26px 28px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(var(--color-secondary-rgb), 0.96), rgba(var(--color-secondary-rgb), 0.84));
            color: #fff;
            box-shadow: var(--shadow-card);
        }
        .page-header h1,
        .page-header p,
        .page-header .eyebrow {
            color: #fff;
        }
        .eyebrow {
            display: inline-block;
            margin-bottom: 10px;
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            opacity: 0.72;
        }
        .header-copy { max-width: 700px; }
        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .header-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .stat-chip {
            min-width: 120px;
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .stat-chip strong {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }
        .notice {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid transparent;
        }
        .notice-success {
            background: rgba(34, 197, 94, 0.10);
            border-color: rgba(34, 197, 94, 0.18);
            color: #166534;
        }
        .notice-error {
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.18);
            color: #991b1b;
        }
        .surface-card {
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            box-shadow: var(--shadow-card);
        }
        .alert-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.56);
            backdrop-filter: blur(8px);
            z-index: 9999;
        }
        .alert-modal.is-open {
            display: flex;
        }
        .alert-modal-card {
            width: min(100%, 520px);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(239, 68, 68, 0.18);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }
        .alert-modal-header,
        .alert-modal-body,
        .alert-modal-footer {
            padding: 22px 24px;
        }
        .alert-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(249, 115, 22, 0.10));
            border-bottom: 1px solid rgba(239, 68, 68, 0.12);
        }
        .alert-modal-card[data-alert-type="warning"] {
            border-color: rgba(217, 119, 6, 0.18);
        }
        .alert-modal-card[data-alert-type="warning"] .alert-modal-header {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(251, 191, 36, 0.10));
            border-bottom-color: rgba(217, 119, 6, 0.14);
        }
        .alert-modal-title {
            margin: 0;
            font-size: 22px;
            color: #991b1b;
        }
        .alert-modal-card[data-alert-type="warning"] .alert-modal-title,
        .alert-modal-card[data-alert-type="warning"] .alert-modal-close {
            color: #92400e;
        }
        .alert-modal-close {
            border: 0;
            background: transparent;
            color: #991b1b;
            font-size: 28px;
            line-height: 1;
            cursor: pointer;
        }
        .alert-modal-body p {
            margin: 0;
            color: var(--color-text);
        }
        .alert-modal-footer {
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid rgba(var(--color-secondary-rgb), 0.08);
        }
        .table-card {
            padding: 12px;
            overflow: hidden;
        }
        .section-card {
            margin-bottom: 22px;
            padding: 22px;
        }
        .empty-state {
            padding: 48px 24px;
            text-align: center;
        }
        .empty-state strong {
            display: block;
            margin-bottom: 10px;
            color: var(--color-secondary);
            font-size: 20px;
        }
        @media (max-width: 1100px) {
            .app {
                flex-direction: column;
                padding: 14px;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            .panel {
                min-height: auto;
                padding: 20px;
            }
        }
        @media (max-width: 900px) {
            .col-2,
            .col-3,
            .col-4,
            .col-5,
            .col-6,
            .col-7,
            .col-8,
            .col-9,
            .col-10,
            .col-12 {
                grid-column: span 12;
            }
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <h2 class="brand">VMD</h2>
        <p class="brand-subtitle">Painel operacional da autoescola com foco em cadastro, acompanhamento e agendamento.</p>
        @if (auth()->user()?->requiresPasswordChange())
            <a class="menu-link {{ request()->routeIs('password.change.*') ? 'active' : '' }}" href="{{ route('password.change.edit') }}">Trocar senha</a>
        @elseif (auth()->user()?->hasRole(\App\Models\User::ROLE_TEACHER))
            <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Inicio</a>
        @else
            <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Inicio</a>
            <a class="menu-link {{ request()->routeIs('students.index', 'students.create', 'students.edit') ? 'active' : '' }}" href="{{ route('students.index') }}">Alunos</a>
            <a class="menu-link {{ request()->routeIs('teachers.index', 'teachers.create', 'teachers.edit') ? 'active' : '' }}" href="{{ route('teachers.index') }}">Professores</a>
            <a class="menu-link {{ request()->routeIs('vehicles.index', 'vehicles.create', 'vehicles.edit') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">Veiculos</a>
            <a class="menu-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" href="{{ route('appointments.index') }}">Agendamentos</a>
            @if (auth()->user()?->hasRole(\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_ADMINISTRATIVE))
                <a class="menu-link {{ request()->routeIs('lesson-monitoring.index') ? 'active' : '' }}" href="{{ route('lesson-monitoring.index') }}">Controle de aulas</a>
            @endif
            @if (auth()->user()?->isManager())
                <a class="menu-link {{ request()->routeIs('users.index', 'users.create', 'users.edit') ? 'active' : '' }}" href="{{ route('users.index') }}">Usuarios</a>
            @endif
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">Sair</button>
        </form>
    </aside>
    <main class="content">
        <section class="panel">
            @yield('content')
        </section>
    </main>
</div>
<div class="alert-modal {{ session('global_alert.message') ? 'is-open' : '' }}" id="global-error-modal" aria-hidden="{{ session('global_alert.message') ? 'false' : 'true' }}">
    <div class="alert-modal-card" id="global-error-modal-card" data-alert-type="{{ session('global_alert.type', 'error') }}" role="alertdialog" aria-modal="true" aria-labelledby="global-error-modal-title">
        <div class="alert-modal-header">
            <h2 class="alert-modal-title" id="global-error-modal-title">{{ session('global_alert.title', 'Erro no sistema') }}</h2>
            <button class="alert-modal-close" type="button" data-alert-modal-close aria-label="Fechar">&times;</button>
        </div>
        <div class="alert-modal-body">
            <p id="global-error-modal-message">{{ session('global_alert.message') }}</p>
        </div>
        <div class="alert-modal-footer">
            <button class="btn-secondary" type="button" data-alert-modal-close>Fechar</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/global-error-handler.js') }}"></script>
</body>
</html>
