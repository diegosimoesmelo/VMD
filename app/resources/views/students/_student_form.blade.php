@php
    $student = $student ?? null;
    $teachers = $teachers ?? collect();
    $statusOptions = \App\Models\Student::statusOptions();
    $v = fn (string $key) => old($key, $student?->{$key});
    $vDate = old('data_nascimento') ?: ($student?->data_nascimento?->format('Y-m-d') ?? '');
    $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
@endphp

<style>
    .form-shell { display: grid; gap: 22px; }
    .stepper { display: flex; gap: 12px; flex-wrap: wrap; }
    .step-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 999px;
        border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
        background: rgba(255, 255, 255, 0.76);
        color: var(--color-muted-text);
        font-size: 13px;
        font-weight: 700;
    }
    .step-badge.active {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.96), rgba(245, 158, 11, 0.82));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 12px 28px rgba(217, 119, 6, 0.18);
    }
    .form-step { display: none; }
    .form-step.active { display: block; }
    .section-heading { margin-bottom: 16px; }
    .section-heading h2 { margin-bottom: 6px; }
    .section-heading p { margin-bottom: 0; }
    .actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    textarea {
        min-height: 120px;
        resize: vertical;
    }
</style>

<div class="form-shell">
    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">{{ $student ? 'Atualizacao de cadastro' : 'Novo cadastro de aluno' }}</span>
            <h1>{{ $student ? 'Edite os dados do aluno com mais contexto visual' : 'Cadastro de aluno em duas etapas mais claras' }}</h1>
            <p>Interface mais organizada para reduzir erro de preenchimento e deixar os dados principais mais visiveis.</p>
        </div>
        <div class="header-actions">
            @if (! empty($backUrl))
                <a class="btn-secondary" href="{{ $backUrl }}">Voltar</a>
            @endif
        </div>
    </div>

    <div class="stepper">
        <span id="stepBadge1" class="step-badge active">Etapa 1 - Dados pessoais</span>
        <span id="stepBadge2" class="step-badge">Etapa 2 - Complementos</span>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="notice notice-error">Corrija os campos obrigatorios e tente novamente.</p>
    @endif

    <form id="studentForm" method="POST" action="{{ $formAction }}">
        @csrf
        @if (! empty($formMethod) && strtoupper($formMethod) === 'PUT')
            @method('PUT')
        @endif

        <div id="step1" class="form-step active">
            <div class="surface-card section-card">
                <div class="section-heading">
                    <h2>Dados pessoais</h2>
                    <p>Informacoes civis e de contato do aluno.</p>
                </div>
                <div class="form-grid">
                    @if ($student && $student->matricula)
                        <div class="field col-12">
                            <label for="matricula_display">Matricula</label>
                            <input id="matricula_display" type="text" value="{{ $student->matricula }}" readonly>
                        </div>
                    @endif
                    <div class="field col-12">
                        <label for="nome">Nome</label>
                        <input id="nome" name="nome" type="text" placeholder="Digite o nome completo" value="{{ $v('nome') }}" required>
                    </div>
                    <div class="field col-7">
                        <label for="endereco">Endereco</label>
                        <input id="endereco" name="endereco" type="text" placeholder="Rua, avenida, etc." value="{{ $v('endereco') }}" required>
                    </div>
                    <div class="field col-2">
                        <label for="numero">Numero</label>
                        <input id="numero" name="numero" type="text" placeholder="Numero" value="{{ $v('numero') }}">
                    </div>
                    <div class="field col-3">
                        <label for="complemento">Complemento</label>
                        <input id="complemento" name="complemento" type="text" placeholder="Complemento" value="{{ $v('complemento') }}">
                    </div>
                    <div class="field col-4">
                        <label for="bairro">Bairro</label>
                        <input id="bairro" name="bairro" type="text" placeholder="Bairro" value="{{ $v('bairro') }}">
                    </div>
                    <div class="field col-4">
                        <label for="cidade">Cidade</label>
                        <input id="cidade" name="cidade" type="text" placeholder="Cidade" value="{{ $v('cidade') }}">
                    </div>
                    <div class="field col-2">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="">Selecione a UF</option>
                            @foreach ($ufs as $uf)
                                <option value="{{ $uf }}" @selected($v('estado') === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-2">
                        <label for="cep">Cep</label>
                        <input id="cep" name="cep" type="text" placeholder="00000-000" value="{{ $v('cep') }}">
                    </div>
                    <div class="field col-4">
                        <label for="telefone">Telefone</label>
                        <input id="telefone" name="telefone" type="text" placeholder="(81) 99999-9999" value="{{ $v('telefone') }}" required>
                    </div>
                    <div class="field col-3">
                        <label for="data_nascimento">Data de nascimento</label>
                        <input id="data_nascimento" name="data_nascimento" type="date" value="{{ $vDate }}" required>
                    </div>
                    <div class="field col-5">
                        <label for="sexo">Sexo</label>
                        <select id="sexo" name="sexo">
                            <option value="">Selecione</option>
                            <option value="masculino" @selected($v('sexo') === 'masculino')>Masculino</option>
                            <option value="feminino" @selected($v('sexo') === 'feminino')>Feminino</option>
                            <option value="outro" @selected($v('sexo') === 'outro')>Outro</option>
                            <option value="nao_informar" @selected($v('sexo') === 'nao_informar')>Prefiro nao informar</option>
                        </select>
                    </div>
                    <div class="field col-5">
                        <label for="naturalidade">Naturalidade</label>
                        <input id="naturalidade" name="naturalidade" type="text" placeholder="Cidade de nascimento" value="{{ $v('naturalidade') }}">
                    </div>
                    <div class="field col-2">
                        <label for="naturalidade_estado">Estado</label>
                        <select id="naturalidade_estado" name="naturalidade_estado">
                            <option value="">Selecione a UF</option>
                            @foreach ($ufs as $uf)
                                <option value="{{ $uf }}" @selected($v('naturalidade_estado') === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-5">
                        <label for="nacionalidade">Nacionalidade</label>
                        <input id="nacionalidade" name="nacionalidade" type="text" placeholder="Brasileira, etc." value="{{ $v('nacionalidade') }}">
                    </div>
                    <div class="field col-3">
                        <label for="rg">RG</label>
                        <input id="rg" name="rg" type="text" placeholder="Numero do RG" value="{{ $v('rg') }}">
                    </div>
                    <div class="field col-3">
                        <label for="orgao_exp">Orgao exp.</label>
                        <input id="orgao_exp" name="orgao_exp" type="text" placeholder="SSP, DETRAN, etc." value="{{ $v('orgao_exp') }}">
                    </div>
                    <div class="field col-2">
                        <label for="rg_estado">Estado</label>
                        <select id="rg_estado" name="rg_estado">
                            <option value="">Selecione a UF</option>
                            @foreach ($ufs as $uf)
                                <option value="{{ $uf }}" @selected($v('rg_estado') === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="cpf">CPF</label>
                        <input id="cpf" name="cpf" type="text" placeholder="000.000.000-00" value="{{ $v('cpf') }}" required>
                    </div>
                    <div class="field col-4">
                        <label for="estado_civil">Estado civil</label>
                        <select id="estado_civil" name="estado_civil">
                            <option value="">Selecione</option>
                            <option value="solteiro" @selected($v('estado_civil') === 'solteiro')>Solteiro(a)</option>
                            <option value="casado" @selected($v('estado_civil') === 'casado')>Casado(a)</option>
                            <option value="uniao_estavel" @selected($v('estado_civil') === 'uniao_estavel')>Uniao estavel</option>
                            <option value="divorciado" @selected($v('estado_civil') === 'divorciado')>Divorciado(a)</option>
                            <option value="separado" @selected($v('estado_civil') === 'separado')>Separado(a)</option>
                            <option value="viuvo" @selected($v('estado_civil') === 'viuvo')>Viuvo(a)</option>
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="grau_escolaridade">Grau de escolaridade</label>
                        <select id="grau_escolaridade" name="grau_escolaridade">
                            <option value="">Selecione</option>
                            <option value="fundamental_incompleto" @selected($v('grau_escolaridade') === 'fundamental_incompleto')>Fundamental incompleto</option>
                            <option value="fundamental_completo" @selected($v('grau_escolaridade') === 'fundamental_completo')>Fundamental completo</option>
                            <option value="medio_incompleto" @selected($v('grau_escolaridade') === 'medio_incompleto')>Medio incompleto</option>
                            <option value="medio_completo" @selected($v('grau_escolaridade') === 'medio_completo')>Medio completo</option>
                            <option value="tecnico" @selected($v('grau_escolaridade') === 'tecnico')>Tecnico</option>
                            <option value="superior_incompleto" @selected($v('grau_escolaridade') === 'superior_incompleto')>Superior incompleto</option>
                            <option value="superior_completo" @selected($v('grau_escolaridade') === 'superior_completo')>Superior completo</option>
                            <option value="pos_graduacao" @selected($v('grau_escolaridade') === 'pos_graduacao')>Pos-graduacao</option>
                            <option value="mestrado" @selected($v('grau_escolaridade') === 'mestrado')>Mestrado</option>
                            <option value="doutorado" @selected($v('grau_escolaridade') === 'doutorado')>Doutorado</option>
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email" placeholder="Digite o e-mail" value="{{ $v('email') }}">
                    </div>
                </div>
            </div>
            <div class="surface-card section-card">
                <div class="section-heading">
                    <h2>Dados profissionais e familiares</h2>
                    <p>Complementos basicos para concluir a etapa principal.</p>
                </div>
                <div class="form-grid">
                    <div class="field col-5">
                        <label for="empresa">Empresa</label>
                        <input id="empresa" name="empresa" type="text" placeholder="Nome da empresa" value="{{ $v('empresa') }}">
                    </div>
                    <div class="field col-4">
                        <label for="profissao">Profissao</label>
                        <input id="profissao" name="profissao" type="text" placeholder="Profissao" value="{{ $v('profissao') }}">
                    </div>
                    <div class="field col-3">
                        <label for="telefone_profissional">Telefone profissional</label>
                        <input id="telefone_profissional" name="telefone_profissional" type="text" placeholder="(81) 99999-9999" value="{{ $v('telefone_profissional') }}">
                    </div>
                    <div class="field col-6">
                        <label for="nome_pai">Pai</label>
                        <input id="nome_pai" name="nome_pai" type="text" placeholder="Nome do pai" value="{{ $v('nome_pai') }}">
                    </div>
                    <div class="field col-6">
                        <label for="nome_mae">Mae</label>
                        <input id="nome_mae" name="nome_mae" type="text" placeholder="Nome da mae" value="{{ $v('nome_mae') }}" required>
                    </div>
                </div>
            </div>
            <div class="actions">
                <button class="btn" type="button" onclick="goToStep2()">Proxima etapa</button>
            </div>
        </div>

        <div id="step2" class="form-step">
            <div class="surface-card section-card">
                <div class="section-heading">
                    <h2>Complementos adicionais</h2>
                    <p>Defina professor responsavel, servico contratado e observacoes finais.</p>
                </div>
                <div class="form-grid">
                    <div class="field col-4">
                        <label for="teacher_id">Professor responsavel</label>
                        <select id="teacher_id" name="teacher_id">
                            <option value="">Selecione um professor</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string) $v('teacher_id') === (string) $teacher->id)>{{ $teacher->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="status">Status do aluno</label>
                        <select id="status" name="status" required>
                            @foreach ($statusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($v('status') === $statusValue || (! $v('status') && $statusValue === \App\Models\Student::STATUS_THEORY_CLASS))>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="servico_oferecido">Servico oferecido</label>
                        <select id="servico_oferecido" name="servico_oferecido">
                            <option value="">Selecione</option>
                            <option value="primeira_habilitacao" @selected($v('servico_oferecido') === 'primeira_habilitacao')>Primeira habilitacao</option>
                            <option value="adicao_categoria" @selected($v('servico_oferecido') === 'adicao_categoria')>Adicao de categoria</option>
                            <option value="aula_habilitado" @selected($v('servico_oferecido') === 'aula_habilitado')>Aula para habilitado</option>
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="categoria_pretendida">Categoria</label>
                        <select id="categoria_pretendida" name="categoria_pretendida">
                            <option value="">Selecione</option>
                            <option value="A" @selected($v('categoria_pretendida') === 'A')>A</option>
                            <option value="B" @selected($v('categoria_pretendida') === 'B')>B</option>
                            <option value="AB" @selected($v('categoria_pretendida') === 'AB')>AB</option>
                        </select>
                    </div>
                    <div class="field col-4">
                        <label for="valor_pago">Valor pago</label>
                        <input id="valor_pago" name="valor_pago" type="number" step="0.01" min="0" placeholder="0,00" value="{{ $v('valor_pago') }}">
                    </div>
                    <div class="field col-4">
                        <label for="quantidade_aulas_a_contratadas">Aulas A contratadas</label>
                        <input
                            id="quantidade_aulas_a_contratadas"
                            name="quantidade_aulas_a_contratadas"
                            type="number"
                            min="0"
                            step="1"
                            placeholder="Ex.: 10"
                            value="{{ $v('quantidade_aulas_a_contratadas') }}"
                        >
                    </div>
                    <div class="field col-4">
                        <label for="quantidade_aulas_b_contratadas">Aulas B contratadas</label>
                        <input
                            id="quantidade_aulas_b_contratadas"
                            name="quantidade_aulas_b_contratadas"
                            type="number"
                            min="0"
                            step="1"
                            placeholder="Ex.: 20"
                            value="{{ $v('quantidade_aulas_b_contratadas') }}"
                        >
                    </div>
                    <div class="field col-12">
                        <label for="observacao">Observacao</label>
                        <textarea id="observacao" name="observacao" placeholder="Escreva observacoes adicionais">{{ $v('observacao') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="actions">
                <button class="btn-secondary" type="button" onclick="goToStep1()">Voltar</button>
                @if (! empty($backUrl))
                    <a class="btn-secondary" href="{{ $backUrl }}">Cancelar</a>
                @endif
                <button class="btn" type="submit">{{ $submitLabel ?? 'Salvar aluno' }}</button>
            </div>
        </div>
    </form>
</div>

<script src="{{ asset('js/student-form.js') }}"></script>
