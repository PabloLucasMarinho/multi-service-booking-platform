<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class ServiceController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    Gate::authorize('viewAny', Service::class);

    $services = Service::query()
      ->with('categories')
      ->orderBy('name')
      ->get();

    $categories = Category::query()
      ->orderBy('name')
      ->get();

    return view('services.index', compact('services', 'categories'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreServiceRequest $request)
  {
    Gate::authorize('create', Service::class);

    try {
      $service = Service::create([
        'name' => $request->name,
        'price' => $request->price,
      ]);

      if ($request->categories) {
        $service->categories()->attach($request->categories);
      }

      return redirect()
        ->route('services.index')
        ->with('success', 'Serviço cadastrado com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao criar funcionário.', [
        'exception' => $e,
      ]);
    }

    return back()
      ->withInput()
      ->with('error', 'Erro ao cadastrar serviço. Tente novamente.');
  }

  /**
   * Display the specified resource.
   */
  public function show(Service $service)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Service $service)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateServiceRequest $request, Service $service)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Service $service)
  {
    //
  }
}
