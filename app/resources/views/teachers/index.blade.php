@extends('layouts.panel', ['title' => 'Professores cadastrados'])

@section('content')
    <style>
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table.teachers {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--font-size-base);
        }

        table.teachers th,
        table.teachers td {
            border: 1px solid var(--color-tertiary);
            padding: 10px 12px;
            text-align: left;
        }

        table.teachers th {
            background: var(--color-background);
            color: var(--color-secondary);
            font-weight: 600;
        }

        table.teachers tr:nth-child(even) td {
            background: #fafafa;
        }

        .btn-sm {
            border: 0;
            border-radius: 8px;
            background: var(--color-primary);
            color: #fff;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .empty {
            color: var(--color-muted-text);
            padding: 24px 0;
        }
    </style>

    <div class="toolbar">
        <h1 style="margin: 0;">Professores cadastrados</h1>
        <a class="btn" href="{{ route('teachers.create') }}">Cadastrar professor</a>
    </div>

    @if (session('success'))
        <p style="background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 10px 12px; border-radius: 8px;">
            {{ session('success') }}
        </p>
    @endif

    @if ($teachers->isEmpty())
        <p class="empty">Nenhum professor cadastrado ainda.</p>
    @else
        <div class="table-wrap">
            <table class="teachers">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Categorias</th>
                        <th>Turnos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td>{{ $teacher->nome }}</td>
                            <td>{{ $teacher->cpf }}</td>
                            <td>{{ $teacher->telefone }}</td>
                            <td>{{ implode(', ', $teacher->categorias_ensino ?? []) }}</td>
                            <td>{{ implode(', ', array_map(fn ($shift) => ucfirst($shift), $teacher->turnos_disponiveis ?? [])) }}</td>
                            <td>
                                <a class="btn-sm" href="{{ route('teachers.edit', $teacher) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
