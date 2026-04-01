<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller
{
  /**
   * Display the specified resource.
   */
  public function show()
  {
    Gate::authorize('view', Company::class);

    $company = Company::first() ?? new Company();

    return view('company.index', compact('company'));
  }

  /**
   * Store a newly created resource in storage or update an already created one.
   */
  public function store(StoreCompanyRequest $request)
  {
    Gate::authorize('update', Company::class);

    Company::updateOrCreate(
      ['id' => 1],
      $request->validated()
    );

    return redirect()
      ->route('company.index')
      ->with('success', 'Dados do estabelecimento salvos com sucesso!');
  }
}
