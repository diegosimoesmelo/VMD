@extends('layouts.panel', ['title' => 'Alunos cadastrados'])

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

        table.students {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--font-size-base);
        }

        table.students th,
        table.students td {
            border: 1px solid var(--color-tertiary);
            padding: 10px 12px;
            text-align: left;
        }

        table.students th {
            background: var(--color-background);
            color: var(--color-secondary);
            font-weight: 600;
        }

        table.students tr:nth-child(even) td {
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
        <h1 style="margin: 0;">Alunos cadastrados</h1>
        <a class="btn" href="{{ route('students.create') }}">Cadastrar aluno</a>
    </div>

    @if (session('success'))
        <p style="background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 10px 12px; border-radius: 8px;">
            {{ session('success') }}
        </p>
    @endif

    @if ($students->isEmpty())
        <p class="empty">Nenhum aluno cadastrado ainda.</p>
    @else
        <div class="table-wrap">
            <table class="students">
                <thead>
                    <tr>
                        <th>Matricula</th>
                        <th>Nome</th>
                        <th>Professor</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Cidade</th>
                        <th>Data nasc.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->matricula ?? '—' }}</td>
                            <td>{{ $student->nome }}</td>
                            <td>{{ $student->teacher?->nome ?? '—' }}</td>
                            <td>{{ $student->cpf }}</td>
                            <td>{{ $student->telefone }}</td>
                            <td>{{ $student->cidade ?? '—' }}</td>
                            <td>{{ $student->data_nascimento?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <a class="btn-sm" href="{{ route('students.edit', $student) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
