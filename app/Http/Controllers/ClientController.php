<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClientController extends Controller
{
  public function __construct(
    private ClientService $clientService
  )
  {
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    Gate::authorize('viewAny', Client::class);

    $clients = Client::query()
      ->orderBy('name')
      ->get();

    return view('clients.index', compact('clients'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    Gate::authorize('create', Client::class);

    return view('clients.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreClientRequest $request)
  {
    Gate::authorize('create', Client::class);

    try {
      $this->clientService->create($request->validated(), $request->user()->uuid);

      return redirect()
        ->route('clients.index')
        ->with('success', 'Cliente cadastrado com sucesso!');

    } catch (Throwable $e) {
      Log::error('Erro ao cadastrar cliente.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao cadastrar cliente. Tente novamente.');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Client $client)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Client $client)
  {
    Gate::authorize('update', $client);

    return view('clients.edit', compact('client'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateClientRequest $request, Client $client)
  {
    Gate::authorize('update', $client);

    try {
      $this->clientService->update($request->validated(), $client);

      return redirect()
        ->route('clients.index')
        ->with('success', 'Cliente editado com sucesso!');

    } catch (Throwable $e) {
      Log::error('Erro ao editar cliente.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao editar cliente.');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Client $client)
  {
    Gate::authorize('delete', $client);

    $client->delete();

    return redirect()->route('clients.index');
  }
}
