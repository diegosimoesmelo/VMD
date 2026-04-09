<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | VMD</title>
    @include('partials.theme-style')
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            color: var(--color-text);
            background:
                radial-gradient(circle at top left, rgba(217, 119, 6, 0.18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(var(--color-secondary-rgb), 0.18), transparent 34%),
                linear-gradient(135deg, #f7f1e7 0%, #f3ede2 48%, #ece5db 100%);
            display: grid;
            place-items: center;
            padding: 28px;
        }

        .login-shell {
            width: min(1120px, 100%);
            min-height: min(720px, calc(100vh - 56px));
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(380px, 0.95fr);
            border-radius: 32px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(var(--color-secondary-rgb), 0.08);
            box-shadow: 0 32px 80px rgba(var(--color-secondary-rgb), 0.16);
            backdrop-filter: blur(18px);
        }

        .brand-panel {
            position: relative;
            padding: 56px;
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.16), transparent 34%),
                linear-gradient(150deg, rgba(var(--color-secondary-rgb), 0.98), rgba(var(--color-secondary-rgb), 0.86));
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            inset: auto -80px 56px auto;
            width: 240px;
            height: 240px;
            border-radius: 40px;
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.34), rgba(255, 255, 255, 0.06));
            transform: rotate(18deg);
        }

        .brand-top,
        .brand-bottom {
            position: relative;
            z-index: 1;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .brand-title {
            margin: 22px 0 14px;
            font-size: clamp(40px, 5vw, 64px);
            line-height: 0.95;
            letter-spacing: -0.04em;
        }

        .brand-copy {
            max-width: 520px;
            font-size: 17px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.78);
        }

        .brand-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            max-width: 520px;
        }

        .brand-stat {
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .brand-stat strong {
            display: block;
            margin-bottom: 6px;
            font-size: 22px;
            color: #fff;
        }

        .brand-stat span {
            color: rgba(255, 255, 255, 0.72);
            font-size: 13px;
            line-height: 1.5;
        }

        .login-panel {
            padding: 56px 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.86);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 14px;
            color: var(--color-primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(32px, 4vw, 44px);
            line-height: 1;
            letter-spacing: -0.04em;
            color: var(--color-secondary);
        }

        .intro {
            margin: 0 0 28px;
            color: var(--color-muted-text);
            line-height: 1.7;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 16px;
            color: #991b1b;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            font-size: 13px;
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-secondary);
            font-size: 13px;
            font-weight: 700;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            height: 54px;
            padding: 0 16px;
            border-radius: 16px;
            border: 1px solid rgba(var(--color-secondary-rgb), 0.10);
            background: rgba(255, 255, 255, 0.92);
            color: var(--color-text);
            font: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: rgba(217, 119, 6, 0.60);
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.12);
            transform: translateY(-1px);
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 6px 0 24px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--color-muted-text);
            font-size: 14px;
        }

        .remember input {
            accent-color: var(--color-primary);
        }

        .row a {
            color: var(--color-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .row a:hover {
            color: var(--color-primary);
        }

        .btn {
            width: 100%;
            height: 56px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: #fff;
            font: inherit;
            font-weight: 800;
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 18px 30px rgba(217, 119, 6, 0.24);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 34px rgba(217, 119, 6, 0.28);
            filter: saturate(1.03);
        }

        .login-footnote {
            margin-top: 18px;
            color: var(--color-muted-text);
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 980px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                min-height: 320px;
                padding: 36px 28px;
            }

            .login-panel {
                padding: 36px 28px;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 14px;
            }

            .login-shell {
                min-height: auto;
                border-radius: 24px;
            }

            .brand-panel,
            .login-panel {
                padding: 28px 22px;
            }

            .brand-grid {
                grid-template-columns: 1fr;
            }

            .row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel">
            <div class="brand-top">
                <span class="brand-mark">VMD Sistema</span>
                <h2 class="brand-title">Vença o Medo de Dirigir.</h2>
                <p class="brand-copy">
                    Centralize alunos, professores, veículos e agendamentos em um ambiente mais claro, rápido e organizado para a operação diária.
                </p>
            </div>

            <div class="brand-bottom">
                <div class="brand-grid">
                    <div class="brand-stat">
                        <strong>VMD</strong>
                        <span>Visão central da operação, com foco em agenda e acompanhamento dos alunos.</span>
                    </div>
                    <div class="brand-stat">
                        <strong>Agenda</strong>
                        <span>Planejamento semanal de professores e veículos com leitura rápida e fluxo contínuo.</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <span class="eyebrow">Acesso ao sistema</span>
                <h1>Entrar no VMD</h1>
                <p class="intro">Use suas credenciais para acessar a operação da autoescola.</p>

                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="field">
                        <label for="username">Usuário</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
                    </div>

                    <div class="field">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="row">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Lembrar-me
                        </label>
                        <a href="{{ route('password.request') }}">Esqueci a senha</a>
                    </div>

                    <button class="btn" type="submit">Entrar no sistema</button>
                </form>

                <p class="login-footnote">VMD · Plataforma de gestão para autoescola</p>
            </div>
        </section>
    </main>
</body>
</html>

