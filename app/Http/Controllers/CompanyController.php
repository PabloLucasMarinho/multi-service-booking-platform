<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;

class CompanyController extends Controller
{
  /**
   * Display the specified resource.
   */
  public function show()
  {
    $company = Company::first() ?? new Company();

    return view('company.index', compact('company'));
  }

  /**
   * Store a newly created resource in storage or update an already created one.
   */
  public function store(StoreCompanyRequest $request)
  {
    Company::updateOrCreate(
      ['id' => 1],
      $request->validated()
    );

    return redirect()
      ->route('company.index')
      ->with('success', 'Dados do estabelecimento salvos com sucesso!');
  }
}
