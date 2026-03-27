<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserController extends Controller
{
  public function __construct(
    private UserService $userService
  )
  {
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    Gate::authorize('viewAny', User::class);

    $users = User::where('uuid', '!=', auth()->user()->uuid)->get();

    return view('users.index', compact('users'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    Gate::authorize('create', User::class);

    return view('users.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreUserRequest $request)
  {
    Gate::authorize('create', User::class);

    if ($request->deleted_user_data) {
      $deletedUser = $request->deleted_user_data;
      return response()->json([
        'deleted_user' => true,
        'uuid' => $deletedUser->uuid,
        'name' => $deletedUser->name,
      ]);
    }

    try {
      $this->userService->create($request->validated());

      if ($request->wantsJson()) {
        return response()->json(['success' => true]);
      }

      return redirect()
        ->route('users.index')
        ->with('success', 'Funcionário cadastrado com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao criar funcionário.', [
        'exception' => $e,
      ]);

      if ($request->wantsJson()) {
        return response()->json(['error' => true], 500);
      }

      return back()
        ->withInput()
        ->with('error', 'Erro ao cadastrar funcionário. Tente novamente.');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(User $user)
  {
    // Verifica se é o próprio perfil ou se tem permissão de view
    if (auth()->user()->uuid === $user->uuid) {
      Gate::authorize('viewSelf', $user);
    } else {
      Gate::authorize('view', $user);
    }

    $user->load('details');

    return view('users.show', compact('user'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(User $user)
  {
    Gate::authorize('update', $user);

    $user->load('details', 'role');

    return view('users.edit', compact('user'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateUserRequest $request, User $user)
  {
    Gate::authorize('update', $user);

    try {
      $this->userService->update($request->validated(), $user);

      if ($user->uuid === auth()->user()->uuid) {
        return redirect()
          ->route('users.show', $user)
          ->with('success', 'Cadastro alterado com sucesso!');
      }

      return redirect()
        ->route('users.index')
        ->with('success', 'Cadastro de funcionário alterado com sucesso!');

    } catch (Throwable $e) {
      Log::error('Erro ao alterar cadastro de funcionário.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao alterar cadastro de funcionário. Tente novamente.');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(User $user)
  {
    Gate::authorize('delete', $user);

    $user->delete();

    return redirect()->route('users.index');
  }

  public function anonymize(string $uuid)
  {
    $user = User::onlyTrashed()->where('uuid', $uuid)->firstOrFail();

    Gate::authorize('delete', $user);

    $user->update([
      'email' => null,
    ]);

    $user->details()->withTrashed()->update([
      'document' => null,
    ]);

    return response()->json(['success' => true]);
  }

  public function restore(string $uuid)
  {
    $user = User::onlyTrashed()->where('uuid', $uuid)->firstOrFail();

    Gate::authorize('restore', $user);

    $user->restore();

    return response()->json(['success' => true]);
  }
}
