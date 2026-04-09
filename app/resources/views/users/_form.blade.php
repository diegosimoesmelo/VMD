@php
    $user = $user ?? null;
    $v = fn (string $key) => old($key, $user?->{$key});
    $roleOptions = \App\Models\User::administrativeRoleOptions();
@endphp

<style>
    .user-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0 16px;
    }
    .user-field { grid-column: span 12; }
    .user-col-6 { grid-column: span 6; }
    .user-col-12 { grid-column: span 12; }
    .actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    @media (max-width: 900px) {
        .user-col-6,
        .user-col-12 {
            grid-column: span 12;
        }
    }
</style>

<div class="form-shell">
    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">{{ $user ? 'Atualização de usuário' : 'Novo usuário administrativo' }}</span>
            <h1>{{ $user ? 'Atualize o acesso do usuário ao painel' : 'Cadastro de usuários do sistema' }}</h1>
            <p>Somente o gerente pode gerenciar acessos e definir o papel de cada usuário interno.</p>
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
                <h2>Dados de acesso</h2>
                <p>Nome, usuário e papel no sistema. Novos usuários saem com a senha inicial padrão e precisam trocá-la no primeiro acesso.</p>
            </div>

            <div class="user-form-grid">
                <div class="user-field user-col-6">
                    <label for="name">Nome</label>
                    <input id="name" name="name" type="text" placeholder="Digite o nome completo" value="{{ $v('name') }}" required>
                </div>
                <div class="user-field user-col-6">
                    <label for="username">Usuário</label>
                    <input id="username" name="username" type="text" placeholder="Digite o nome de usuário" value="{{ $v('username') }}" required>
                </div>
                <div class="user-field user-col-6">
                    <label for="role">Perfil de acesso</label>
                    <select id="role" name="role" required>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($v('role') ?: \App\Models\User::ROLE_ADMINISTRATIVE) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="user-field user-col-6">
                    <label>Senha inicial</label>
                    <input type="text" value="vmdcfc" readonly>
                    <p>O usuário será obrigado a trocar essa senha no primeiro acesso. A nova senha precisa ter pelo menos 6 caracteres.</p>
                </div>
            </div>
        </div>

        <div class="actions">
            @if (! empty($backUrl))
                <a class="btn-secondary" href="{{ $backUrl }}">Cancelar</a>
            @endif
            <button class="btn" type="submit">{{ $submitLabel ?? 'Salvar usuário' }}</button>
        </div>
    </form>

    @if ($user)
        <div class="surface-card section-card">
            <div class="section-heading">
                <h2>Redefinição de senha</h2>
                <p>Use esta ação quando precisar devolver o acesso do usuário para a senha padrão do sistema.</p>
            </div>

            <p>A senha será redefinida para <strong>vmdcfc</strong> e o usuário será obrigado a trocá-la no próximo login.</p>

            <form method="POST" action="{{ route('users.reset-password', $user) }}">
                @csrf
                <button class="btn-secondary" type="submit">Resetar senha para vmdcfc</button>
            </form>
        </div>
    @endif
</div>


