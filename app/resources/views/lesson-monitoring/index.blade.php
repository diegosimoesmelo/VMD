@extends('layouts.panel', ['title' => 'Controle de aulas'])

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
        .vehicle-summary,
        .agenda-week-nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 18px;
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
        .monitor-slot-card {
            border-radius: 18px;
            padding: 14px;
            min-height: 220px;
            display: grid;
            gap: 10px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
        }
        .monitor-slot-card.unavailable {
            background: rgba(var(--color-secondary-rgb), 0.08);
            border-color: rgba(var(--color-secondary-rgb), 0.14);
        }
        .monitor-slot-card.empty {
            background: rgba(148, 163, 184, 0.08);
            border-color: rgba(148, 163, 184, 0.18);
        }
        .monitor-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            width: fit-content;
            font-size: 12px;
            font-weight: 700;
        }
        .monitor-status.scheduled { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
        .monitor-status.completed { background: rgba(34, 197, 94, 0.12); color: #166534; }
        .monitor-status.absent { background: rgba(249, 115, 22, 0.14); color: #9a3412; }
        .monitor-status.vehicle-issue { background: rgba(239, 68, 68, 0.12); color: #991b1b; }
        .monitor-status.unavailable,
        .monitor-status.empty { background: rgba(148, 163, 184, 0.14); color: #334155; }
        .monitor-meta {
            display: grid;
            gap: 4px;
        }
        .monitor-meta strong {
            color: var(--color-secondary);
        }
        .lesson-monitoring-form {
            display: grid;
            gap: 8px;
        }
        .lesson-monitoring-form select,
        .lesson-monitoring-form textarea {
            margin-bottom: 0;
            padding: 10px 12px;
            font-size: 14px;
        }
        .lesson-monitoring-form textarea {
            min-height: 80px;
        }
        .monitor-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .empty-agenda {
            padding: 32px;
            text-align: center;
        }
    </style>

    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Controle operacional</span>
            <h1>Acompanhamento das aulas por grade de veiculo</h1>
            <p>Gerente e administrativo podem acompanhar a grade da semana, verificar o que ja deveria ter acontecido e apontar ausencia do aluno ou problema com o carro quando necessario.</p>
        </div>
    </div>

    <div id="lesson-monitoring-page-content">
        @include('lesson-monitoring._content')
    </div>
    <script src="{{ asset('js/lesson-monitoring-page.js') }}"></script>
@endsection
