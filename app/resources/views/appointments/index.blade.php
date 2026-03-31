@extends('layouts.panel', ['title' => 'Agenda semanal'])

@section('content')
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
    <div id="appointments-page-content">
        @include('appointments._agenda_content')
    </div>
    <script src="{{ asset('js/appointments-page.js') }}"></script>
@endsection
