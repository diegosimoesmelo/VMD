@php
    $student = $student ?? null;
    $teachers = $teachers ?? collect();
    $v = fn (string $key) => old($key, $student?->{$key});
    $vDate = old('data_nascimento') ?: ($student?->data_nascimento?->format('Y-m-d') ?? '');
    $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
@endphp

<style>
    .stepper {
        display: flex;
        gap: 10px;
        margin: 0 0 16px;
        flex-wrap: wrap;
    }

    .step-badge {
        border: 1px solid var(--color-tertiary);
        background: var(--color-surface);
        color: var(--color-muted-text);
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 13px;
    }

    .step-badge.active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: #fff;
    }

    .form-step {
        display: none;
    }

    .form-step.active {
        display: block;
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

    textarea {
        width: 100%;
        border: 1px solid var(--color-tertiary);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        font: inherit;
        background: #fff;
        color: var(--color-text);
        resize: vertical;
        min-height: 110px;
    }
</style>

<div class="stepper">
    <span id="stepBadge1" class="step-badge active">Etapa 1 - Dados pessoais</span>
    <span id="stepBadge2" class="step-badge">Etapa 2 - Complementos</span>
</div>

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

<form id="studentForm" method="POST" action="{{ $formAction }}">
    @csrf
    @if (! empty($formMethod) && strtoupper($formMethod) === 'PUT')
        @method('PUT')
    @endif

    <div id="step1" class="form-step active">
        <h2>Dados Pessoais</h2>
        <div class="form-grid">
            @if ($student && $student->matricula)
                <div class="field col-12">
                    <label for="matricula_display">Matricula</label>
                    <input id="matricula_display" type="text" value="{{ $student->matricula }}" readonly style="background: #f1f5f9; cursor: default;">
                </div>
            @endif
            <div class="field col-12">
                <label for="nome">Nome</label>
                <input id="nome" name="nome" type="text" placeholder="Digite o nome completo" value="{{ $v('nome') }}" required>
            </div>

            <div class="field col-7">
                <label for="endereco">Endereço</label>
                <input id="endereco" name="endereco" type="text" placeholder="Rua, avenida, etc." value="{{ $v('endereco') }}" required>
            </div>
            <div class="field col-2">
                <label for="numero">Número</label>
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
                <label for="data_nascimento">Data Nascimento</label>
                <input id="data_nascimento" name="data_nascimento" type="date" value="{{ $vDate }}" required>
            </div>
            <div class="field col-5">
                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo">
                    <option value="">Selecione</option>
                    <option value="masculino" @selected($v('sexo') === 'masculino')>Masculino</option>
                    <option value="feminino" @selected($v('sexo') === 'feminino')>Feminino</option>
                    <option value="outro" @selected($v('sexo') === 'outro')>Outro</option>
                    <option value="nao_informar" @selected($v('sexo') === 'nao_informar')>Prefiro não informar</option>
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
                <label for="orgao_exp">Orgao Exp.</label>
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
                <label for="estado_civil">Estado Civil</label>
                <select id="estado_civil" name="estado_civil">
                    <option value="">Selecione</option>
                    <option value="solteiro" @selected($v('estado_civil') === 'solteiro')>Solteiro(a)</option>
                    <option value="casado" @selected($v('estado_civil') === 'casado')>Casado(a)</option>
                    <option value="uniao_estavel" @selected($v('estado_civil') === 'uniao_estavel')>Uniao estável</option>
                    <option value="divorciado" @selected($v('estado_civil') === 'divorciado')>Divorciado(a)</option>
                    <option value="separado" @selected($v('estado_civil') === 'separado')>Separado(a)</option>
                    <option value="viuvo" @selected($v('estado_civil') === 'viuvo')>Viúvo(a)</option>
                </select>
            </div>
            <div class="field col-4">
                <label for="grau_escolaridade">Grau Escolaridade</label>
                <select id="grau_escolaridade" name="grau_escolaridade">
                    <option value="">Selecione</option>
                    <option value="fundamental_incompleto" @selected($v('grau_escolaridade') === 'fundamental_incompleto')>Fundamental incompleto</option>
                    <option value="fundamental_completo" @selected($v('grau_escolaridade') === 'fundamental_completo')>Fundamental completo</option>
                    <option value="medio_incompleto" @selected($v('grau_escolaridade') === 'medio_incompleto')>Medio incompleto</option>
                    <option value="medio_completo" @selected($v('grau_escolaridade') === 'medio_completo')>Medio completo</option>
                    <option value="tecnico" @selected($v('grau_escolaridade') === 'tecnico')>Técnico</option>
                    <option value="superior_incompleto" @selected($v('grau_escolaridade') === 'superior_incompleto')>Superior incompleto</option>
                    <option value="superior_completo" @selected($v('grau_escolaridade') === 'superior_completo')>Superior completo</option>
                    <option value="pos_graduacao" @selected($v('grau_escolaridade') === 'pos_graduacao')>Pos-graduação</option>
                    <option value="mestrado" @selected($v('grau_escolaridade') === 'mestrado')>Mestrado</option>
                    <option value="doutorado" @selected($v('grau_escolaridade') === 'doutorado')>Doutorado</option>
                </select>
            </div>
            <div class="field col-4">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" placeholder="Digite o e-mail" value="{{ $v('email') }}">
            </div>
        </div>

        <h2>Dados Profissionais</h2>
        <div class="form-grid">
            <div class="field col-5">
                <label for="empresa">Empresa</label>
                <input id="empresa" name="empresa" type="text" placeholder="Nome da empresa" value="{{ $v('empresa') }}">
            </div>
            <div class="field col-4">
                <label for="profissao">Profissão</label>
                <input id="profissao" name="profissao" type="text" placeholder="Profissao" value="{{ $v('profissao') }}">
            </div>
            <div class="field col-3">
                <label for="telefone_profissional">Telefone</label>
                <input id="telefone_profissional" name="telefone_profissional" type="text" placeholder="(81) 99999-9999" value="{{ $v('telefone_profissional') }}">
            </div>
        </div>

        <h2>Nome dos Pais</h2>
        <div class="form-grid">
            <div class="field col-6">
                <label for="nome_pai">Pai</label>
                <input id="nome_pai" name="nome_pai" type="text" placeholder="Nome do pai" value="{{ $v('nome_pai') }}">
            </div>
            <div class="field col-6">
                <label for="nome_mae">Mãe</label>
                <input id="nome_mae" name="nome_mae" type="text" placeholder="Nome da mae" value="{{ $v('nome_mae') }}" required>
            </div>
        </div>
        <div class="actions">
            <button class="btn" type="button" onclick="goToStep2()">Próxima etapa</button>
        </div>
    </div>

    <div id="step2" class="form-step">
        <h2>Complementos adicionais</h2>
        <div class="form-grid">
            <div class="field col-4">
                <label for="teacher_id">Professor responsável</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">Selecione um professor</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) $v('teacher_id') === (string) $teacher->id)>{{ $teacher->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field col-4">
                <label for="servico_oferecido">Serviço oferecido</label>
                <select id="servico_oferecido" name="servico_oferecido">
                    <option value="">Selecione</option>
                    <option value="primeira_habilitacao" @selected($v('servico_oferecido') === 'primeira_habilitacao')>1ª Habilitação</option>
                    <option value="adicao_categoria" @selected($v('servico_oferecido') === 'adicao_categoria')>Adição de categoria</option>
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
            <div class="field col-12">
                <label for="observacao">Observação</label>
                <textarea id="observacao" name="observacao" placeholder="Escreva observações adicionais">{{ $v('observacao') }}</textarea>
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

<script src="{{ asset('js/student-form.js') }}"></script>
