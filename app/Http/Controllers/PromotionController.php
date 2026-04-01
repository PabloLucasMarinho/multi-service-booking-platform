<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class PromotionController extends Controller
{
  public function __construct(
    private PromotionService $promotionService
  )
  {
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    Gate::authorize('viewAny', Promotion::class);

    $promotions = Promotion::query()
      ->orderBy('name')
      ->get();

    return view('promotions.index', compact('promotions'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    Gate::authorize('create', Promotion::class);

    return view('promotions.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StorePromotionRequest $request)
  {
    Gate::authorize('create', Promotion::class);

    try {
      $this->promotionService->create($request->validated());

      return redirect()
        ->route('promotions.index')
        ->with('success', 'Promoção cadastrada com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao ao criar promoção.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao cadastrar promoção. Tente novamente.');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Promotion $promotion)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Promotion $promotion)
  {
    Gate::authorize('update', $promotion);

    $promotion->load('categories');

    return view('promotions.edit', compact('promotion'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdatePromotionRequest $request, Promotion $promotion)
  {
    Gate::authorize('update', $promotion);

    try {
      $this->promotionService->update($request->validated(), $promotion);

      return redirect()
        ->route('promotions.index')
        ->with('success', 'Promoção atualizada com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao ao atualizar promoção.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao atualizar promoção. Tente novamente.');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Promotion $promotion)
  {
    Gate::authorize('delete', $promotion);

    $promotion->delete();

    return redirect()->route('promotions.index');
  }
}
