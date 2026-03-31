<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $categoryUuids = collect($request->categories)->map(function ($name) {
          $name = Str::title(trim($name));

          return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
          )->uuid;
        });

        $service->categories()->attach($categoryUuids);
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
   * Update the specified resource in storage.
   */
  public function update(UpdateServiceRequest $request, Service $service)
  {
    Gate::authorize('update', $service);

    try {
      $service->update([
        'name' => $request->name,
        'price' => $request->price,
      ]);

      if ($request->categories) {
        $categoryUuids = collect($request->categories)->map(function ($name) {
          $name = Str::title(trim($name));
          return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
          )->uuid;
        });

        $service->categories()->sync($categoryUuids);
      } else {
        $service->categories()->detach();
      }

      return redirect()
        ->route('services.index')
        ->with('success', 'Serviço atualizado com sucesso!');

    } catch (Throwable $e) {
      Log::error('Erro ao atualizar serviço.', ['exception' => $e]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao atualizar serviço. Tente novamente.');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Service $service)
  {
    Gate::authorize('delete', $service);

    $service->delete();

    return redirect()
      ->route('services.index')
      ->with('success', 'Serviço apagado com sucesso!');
  }
}
