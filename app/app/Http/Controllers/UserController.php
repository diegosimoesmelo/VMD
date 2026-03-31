<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    private const DEFAULT_PASSWORD = 'vmdcfc';

    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->orderBy('username')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'password' => self::DEFAULT_PASSWORD,
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario administrativo cadastrado com sucesso. Senha inicial: '.self::DEFAULT_PASSWORD);
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->rules($user));

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario administrativo atualizado com sucesso.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $user->update([
            'password' => self::DEFAULT_PASSWORD,
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('users.edit', $user)
            ->with('success', 'Senha resetada com sucesso para '.self::DEFAULT_PASSWORD.'. O usuario precisara troca-la no proximo acesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user?->id),
            ],
            'role' => ['required', 'string', Rule::in(array_keys(User::manageableRoleOptions()))],
        ];
    }
}
