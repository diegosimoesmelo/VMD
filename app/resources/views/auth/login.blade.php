<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @include('partials.theme-style')
    <style>
        body {
            margin: 0;
            font-family: var(--font-family-base);
            font-size: var(--font-size-base);
            background: var(--color-background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: var(--color-surface);
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 28px;
            border: 1px solid var(--color-tertiary);
        }

        h1 {
            margin: 0 0 20px;
            font-size: var(--font-size-title);
            color: var(--color-secondary);
        }

        .field {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: var(--color-secondary);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-tertiary);
            border-radius: 8px;
            font-size: var(--font-size-base);
            box-sizing: border-box;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .row a {
            font-size: 13px;
            color: var(--color-primary);
            text-decoration: none;
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: var(--color-primary);
            color: #fff;
            padding: 10px;
            font-size: var(--font-size-base);
            cursor: pointer;
        }

        .error {
            margin-bottom: 12px;
            font-size: 13px;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 8px 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>VMD</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="field">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="row">
                <label>
                    <input type="checkbox" name="remember"> Lembrar-me
                </label>
                <a href="{{ route('password.request') }}">Esqueci a senha</a>
            </div>

            <button class="btn" type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
