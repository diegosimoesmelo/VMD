<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Painel' }}</title>
    @include('partials.theme-style')
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            color: var(--color-text);
            background: var(--color-background);
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 240px;
            background: var(--color-secondary);
            color: #fff;
            padding: 20px 14px;
        }

        .brand {
            margin: 0 0 20px;
            font-size: 20px;
        }

        .menu-link {
            display: block;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
        }

        .menu-link:hover,
        .menu-link.active {
            background: var(--color-primary);
        }

        .logout-btn {
            margin-top: 20px;
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: #dc2626;
            color: #fff;
            padding: 10px 12px;
            cursor: pointer;
        }

        .content {
            flex: 1;
            padding: 28px;
        }

        .panel {
            background: var(--color-surface);
            border-radius: 10px;
            padding: 24px;
            border: 1px solid var(--color-tertiary);
        }

        h1 {
            margin: 0 0 14px;
            font-size: var(--font-size-title);
            color: var(--color-secondary);
        }

        h2 {
            margin: 22px 0 10px;
            font-size: 18px;
            color: var(--color-secondary);
            border-bottom: 1px solid var(--color-tertiary);
            padding-bottom: 6px;
        }

        p {
            margin: 0 0 10px;
            color: var(--color-muted-text);
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: var(--color-secondary);
        }

        input,
        select {
            width: 100%;
            border: 1px solid var(--color-tertiary);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font: inherit;
            background: #fff;
            color: var(--color-text);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 0 12px;
        }

        .field {
            grid-column: span 12;
        }

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

        .btn {
            border: 0;
            border-radius: 8px;
            background: var(--color-primary);
            color: #fff;
            padding: 10px 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <h2 class="brand">VMD</h2>

        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Inicio</a>
        <a class="menu-link {{ request()->routeIs('students.index', 'students.create', 'students.edit') ? 'active' : '' }}" href="{{ route('students.index') }}">Alunos</a>
        <a class="menu-link {{ request()->routeIs('teachers.index') ? 'active' : '' }}" href="{{ route('teachers.index') }}">Professores</a>
        <a class="menu-link {{ request()->routeIs('teachers.create', 'teachers.edit') ? 'active' : '' }}" href="{{ route('teachers.create') }}">Cadastrar professor</a>
        <a class="menu-link {{ request()->routeIs('appointments.index') ? 'active' : '' }}" href="{{ route('appointments.index') }}">Agendamentos</a>

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
</body>
</html>
