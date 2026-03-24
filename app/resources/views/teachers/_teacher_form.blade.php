@php
    $teacher = $teacher ?? null;
    $v = fn (string $key) => old($key, $teacher?->{$key});
    $selectedCategories = old('categorias_ensino', $teacher?->categorias_ensino ?? []);
    $selectedShifts = old('turnos_disponiveis', $teacher?->turnos_disponiveis ?? []);
    $categoryOptions = ['A', 'B', 'C', 'D', 'E', 'AB'];
    $shiftOptions = [
        'manha' => 'Manha',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
        'integral' => 'Integral',
    ];
@endphp

<style>
    .teacher-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0 12px;
    }

    .teacher-field {
        grid-column: span 12;
    }

    .teacher-col-4 { grid-column: span 4; }
    .teacher-col-6 { grid-column: span 6; }
    .teacher-col-12 { grid-column: span 12; }

    .check-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .check-card {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--color-tertiary);
        border-radius: 8px;
        padding: 10px 12px;
        background: #fff;
    }

    .check-card input {
        width: auto;
        margin: 0;
    }

    .actions {
        display: flex;
        gap: 10px;
        margin-top: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-secondary {
        border: 1px solid var(--color-tertiary);
        border-radius: 8px;
        background: var(--color-surface);
        color: var(--color-secondary);
        padding: 10px 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: inherit;
    }

    @media (max-width: 900px) {
        .teacher-col-4,
        .teacher-col-6,
        .teacher-col-12 {
            grid-column: span 12;
        }
    }
</style>

@if (session('success'))
    <p style="background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 10px 12px; border-radius: 8px;">
        {{ session('success') }}
    </p>
@endif

@if ($errors->any())
    <p style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 10px 12px; border-radius: 8px;">
        Corrija os campos obrigatorios e tente novamente.
    </p>
@endif

<form method="POST" action="{{ $formAction }}">
    @csrf
    @if (! empty($formMethod) && strtoupper($formMethod) === 'PUT')
        @method('PUT')
    @endif

    <div class="teacher-form-grid">
        <div class="teacher-field teacher-col-12">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" type="text" placeholder="Digite o nome completo" value="{{ $v('nome') }}" required>
        </div>

        <div class="teacher-field teacher-col-6">
            <label for="cpf">CPF</label>
            <input id="cpf" name="cpf" type="text" placeholder="000.000.000-00" value="{{ $v('cpf') }}" required>
        </div>

        <div class="teacher-field teacher-col-6">
            <label for="telefone">Telefone</label>
            <input id="telefone" name="telefone" type="text" placeholder="(81) 99999-9999" value="{{ $v('telefone') }}" required>
        </div>

        <div class="teacher-field teacher-col-6">
            <label>Categorias que ensina</label>
            <div class="check-grid">
                @foreach ($categoryOptions as $category)
                    <label class="check-card" for="categoria_{{ $category }}">
                        <input
                            id="categoria_{{ $category }}"
                            name="categorias_ensino[]"
                            type="checkbox"
                            value="{{ $category }}"
                            @checked(in_array($category, $selectedCategories, true))
                        >
                        <span>{{ $category }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="teacher-field teacher-col-6">
            <label>Turnos disponiveis</label>
            <div class="check-grid">
                @foreach ($shiftOptions as $shiftValue => $shiftLabel)
                    <label class="check-card" for="turno_{{ $shiftValue }}">
                        <input
                            id="turno_{{ $shiftValue }}"
                            name="turnos_disponiveis[]"
                            type="checkbox"
                            value="{{ $shiftValue }}"
                            @checked(in_array($shiftValue, $selectedShifts, true))
                        >
                        <span>{{ $shiftLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="actions">
        @if (! empty($backUrl))
            <a class="btn-secondary" href="{{ $backUrl }}">Cancelar</a>
        @endif
        <button class="btn" type="submit">{{ $submitLabel ?? 'Salvar professor' }}</button>
    </div>
</form>
