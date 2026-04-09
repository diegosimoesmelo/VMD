@php
    $vehicle = $vehicle ?? null;
    $v = fn (string $key) => old($key, $vehicle?->{$key});
    $categoryOptions = \App\Models\Vehicle::categoryOptions();
@endphp

<style>
    .vehicle-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0 16px;
    }
    .vehicle-field { grid-column: span 12; }
    .vehicle-col-4 { grid-column: span 4; }
    .vehicle-col-6 { grid-column: span 6; }
    .vehicle-col-12 { grid-column: span 12; }
    .actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    @media (max-width: 900px) {
        .vehicle-col-4,
        .vehicle-col-6,
        .vehicle-col-12 {
            grid-column: span 12;
        }
    }
</style>

<div class="form-shell">
    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">{{ $vehicle ? 'Atualização de veículo' : 'Novo cadastro de veículo' }}</span>
            <h1>{{ $vehicle ? 'Atualize o veículo vinculado ao professor' : 'Cadastro de veículo por categoria e placa' }}</h1>
            <p>Organize os veículos da autoescola e prepare a agenda para controlar o uso por horário.</p>
        </div>
        <div class="header-actions">
            @if (! empty($backUrl))
                <a class="btn-secondary" href="{{ $backUrl }}">Voltar</a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="notice notice-error">Corrija os campos obrigatórios e tente novamente.</p>
    @endif

    <form method="POST" action="{{ $formAction }}">
        @csrf
        @if (! empty($formMethod) && strtoupper($formMethod) === 'PUT')
            @method('PUT')
        @endif

        <div class="surface-card section-card">
            <div class="section-heading">
                <h2>Informações do veículo</h2>
                <p>Defina a placa e a categoria de uso do veículo.</p>
            </div>

            <div class="vehicle-form-grid">
                <div class="vehicle-field vehicle-col-6">
                    <label for="placa">Placa</label>
                    <input id="placa" name="placa" type="text" placeholder="ABC1D23" value="{{ $v('placa') }}" required>
                </div>
                <div class="vehicle-field vehicle-col-6">
                    <label for="categoria">Categoria do veículo</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecione a categoria</option>
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}" @selected($v('categoria') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="actions">
            @if (! empty($backUrl))
                <a class="btn-secondary" href="{{ $backUrl }}">Cancelar</a>
            @endif
            <button class="btn" type="submit">{{ $submitLabel ?? 'Salvar veículo' }}</button>
        </div>
    </form>
</div>

