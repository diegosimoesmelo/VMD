@php
    $teacher = $teacher ?? null;
    $v = fn (string $key) => old($key, $teacher?->{$key});
    $selectedCategories = old('categorias_ensino', $teacher?->categorias_ensino ?? []);
    $selectedShifts = old('turnos_disponiveis', $teacher?->turnos_disponiveis ?? []);
    $categoryOptions = \App\Models\Teacher::categoryOptions();
    $shiftOptions = \App\Models\Teacher::shiftOptions();
    $schedulingStatusOptions = \App\Models\Teacher::schedulingStatusOptions();
@endphp

<style>
    .teacher-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0 16px;
    }
    .teacher-field { grid-column: span 12; }
    .teacher-col-4 { grid-column: span 4; }
    .teacher-col-6 { grid-column: span 6; }
    .teacher-col-12 { grid-column: span 12; }
    .check-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .check-card {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
        border-radius: 18px;
        padding: 14px 16px;
        background: rgba(255, 255, 255, 0.92);
    }
    .check-card input {
        width: auto;
        margin: 0;
    }
    .actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    @media (max-width: 900px) {
        .teacher-col-4,
        .teacher-col-6,
        .teacher-col-12 {
            grid-column: span 12;
        }
    }
</style>

<div class="form-shell">
    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">{{ $teacher ? 'Atualização de professor' : 'Novo cadastro de professor' }}</span>
            <h1>{{ $teacher ? 'Atualize o perfil do instrutor com mais clareza visual' : 'Cadastro de professor com foco em disponibilidade' }}</h1>
            <p>Uma tela mais limpa para manter equipe, categorias e turnos organizados no mesmo lugar.</p>
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
                <h2>Informações do professor</h2>
                <p>Dados de identificação, categoria ensinada e turno disponível. Ao salvar, o sistema cria ou atualiza automaticamente o acesso do professor ao painel.</p>
            </div>

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
                    <label>Turnos disponíveis</label>
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
                <div class="teacher-field teacher-col-12">
                    <label for="status_agendamento">Disponibilidade para aparecer na agenda</label>
                    <select id="status_agendamento" name="status_agendamento">
                        @foreach ($schedulingStatusOptions as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(($v('status_agendamento') ?: \App\Models\Teacher::STATUS_AVAILABLE) === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="teacher-field teacher-col-12">
                    <label>Acesso do professor ao sistema</label>
                    <input type="text" value="{{ preg_replace('/\D+/', '', (string) $v('cpf')) ?: 'Será gerado a partir do CPF' }}" readonly>
                    <p>Login inicial: CPF sem pontuação. Senha inicial: <strong>vmdcfc</strong>. No primeiro acesso, o professor precisará trocar a senha e verá apenas sua própria tela inicial com resumo semanal.</p>
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
</div>

