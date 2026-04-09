<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha</title>
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
            max-width: 460px;
            background: var(--color-surface);
            border-radius: 10px;
            padding: 28px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            color: var(--color-text);
            border: 1px solid var(--color-tertiary);
        }

        a {
            color: var(--color-primary);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Esqueci a senha</h1>
        <p>Fluxo de recuperação de senha será implementado na próxima etapa.</p>
        <p><a href="{{ route('login') }}">Voltar para login</a></p>
    </div>
</body>
</html>

