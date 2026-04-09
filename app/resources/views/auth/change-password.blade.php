@extends('layouts.panel', ['title' => 'Primeiro acesso'])

@section('content')
    <div class="page-header">
        <div class="header-copy">
            <span class="eyebrow">Primeiro acesso</span>
            <h1>Defina sua nova senha</h1>
            <p>Por segurança, a senha inicial precisa ser trocada antes de continuar usando o sistema.</p>
        </div>
    </div>

    @if (session('success'))
        <p class="notice notice-success">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <p class="notice notice-error">{{ $errors->first() }}</p>
    @endif

    <div class="surface-card section-card" style="max-width: 720px;">
        <h2>Troca obrigatória de senha</h2>
        <p>Usuário: <strong>{{ $user->name ?: $user->username }}</strong></p>
        <p>A nova senha pode ser simples, mas precisa ter pelo menos 6 caracteres.</p>

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            @method('PUT')

            <label for="password">Nova senha</label>
            <input id="password" name="password" type="password" minlength="6" required>

            <label for="password_confirmation">Confirmar nova senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required>

            <div class="actions" style="display:flex; gap:12px; margin-top:12px; flex-wrap:wrap;">
                <button class="btn" type="submit">Salvar nova senha</button>
            </div>
        </form>
    </div>
@endsection

