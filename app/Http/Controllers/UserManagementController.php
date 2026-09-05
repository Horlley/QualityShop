<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveManagedUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        return view('users.index', ['users' => User::query()
            ->when($request->user()->role !== 'admin', fn ($query) => $query->where('role', 'customer'))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->orderBy('name')->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('users.form', ['managedUser' => new User(['role' => 'customer', 'active' => true])]);
    }

    public function store(SaveManagedUserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->safe()->except('password_confirmation'));
        Log::info('Conta didática criada', ['actor_id' => $request->user()->id, 'user_id' => $user->id]);

        return redirect()->route('users.index')->with('success', 'Conta criada. Use somente dados fictícios.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['managedUser' => $user]);
    }

    public function update(SaveManagedUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('password_confirmation');
        if ($request->user()->id === $user->id && (! $request->boolean('active') || $data['role'] !== 'admin')) {
            return back()->withErrors(['role' => 'Você não pode desativar ou retirar seu próprio acesso administrativo.']);
        }
        if (! $request->filled('password')) {
            unset($data['password']);
        }
        $user->update($data);
        Log::info('Conta didática atualizada', ['actor_id' => $request->user()->id, 'user_id' => $user->id, 'role' => $user->role, 'active' => $user->active]);

        return redirect()->route('users.index')->with('success', 'Conta atualizada. Bloqueios valem também para sessões já abertas.');
    }
}
