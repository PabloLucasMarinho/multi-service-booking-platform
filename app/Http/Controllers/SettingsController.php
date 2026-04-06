<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingsRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
  public function show()
  {
    Gate::authorize('view', Company::class);

    $company = Company::first() ?? new Company();

    return view('settings.index', compact('company'));
  }

  public function store(StoreSettingsRequest $request)
  {
    Gate::authorize('update', Company::class);

    Company::updateOrCreate(
      ['id' => 1],
      $request->validated()
    );

    return redirect()
      ->route('settings.index')
      ->with('success', 'Configurações salvas com sucesso!');
  }
}
