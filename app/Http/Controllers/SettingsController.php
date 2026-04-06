<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingsRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
  public function show()
  {
    Gate::authorize('view', Company::class);

    $company = Company::first() ?? new Company();
    $users = User::with('role')
      ->whereHas('role', fn($q) => $q->where('name', '!=', 'owner'))
      ->orderBy('name')
      ->get()
      ->groupBy('role.name');

    return view('settings.index', compact('company', 'users'));
  }

  public function store(StoreSettingsRequest $request)
  {
    Gate::authorize('update', Company::class);

    $validated = $request->validated();

    Company::updateOrCreate(
      ['id' => 1],
      [
        'rebooking_reminder_days' => $validated['rebooking_reminder_days'] ?? null,
        'max_discount_percentage' => $validated['max_discount_percentage'] ?? null,
      ]
    );

    $authorizedUuids = $validated['discount_users'] ?? [];
    User::query()->update(['can_apply_manual_discount' => false]);
    if (!empty($authorizedUuids)) {
      User::whereIn('uuid', $authorizedUuids)->update(['can_apply_manual_discount' => true]);
    }

    return redirect()
      ->route('settings.index')
      ->with('success', 'Configurações salvas com sucesso!');
  }
}
