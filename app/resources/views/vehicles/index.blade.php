@extends('layouts.panel', ['title' => 'Veiculos cadastrados'])

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
            <span class="eyebrow">Modulo de veiculos</span>
            <h1>Frota organizada por placa e categoria</h1>
            <p>Cadastre os veiculos da autoescola para controlar placa, categoria e uso compartilhado na agenda.</p>
            <div class="header-stats">
                <div class="stat-chip">
                    <strong>{{ $vehicles->count() }}</strong>
                    <span>veiculos cadastrados</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a class="btn" href="{{ route('vehicles.create') }}">Novo veiculo</a>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($vehicles->isEmpty())
        <div class="surface-card empty-state">
            <strong>Nenhum veiculo cadastrado ainda.</strong>
            <p>Cadastre a frota para vincular os agendamentos ao veiculo correto e evitar conflitos de horario.</p>
            <a class="btn" href="{{ route('vehicles.create') }}">Cadastrar primeiro veiculo</a>
        </div>
    @else
        <div class="surface-card table-card">
            <div class="record-table-wrap">
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Categoria</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <span class="record-title">{{ strtoupper($vehicle->placa) }}</span>
                                    <span class="record-subtitle">Veiculo operacional</span>
                                </td>
                                <td>
                                    <span class="tag">{{ \App\Models\Vehicle::categoryOptions()[$vehicle->categoria] ?? $vehicle->categoria }}</span>
                                </td>
                                <td>
                                    <a class="btn-secondary" href="{{ route('vehicles.edit', $vehicle) }}">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
