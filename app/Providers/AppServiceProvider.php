<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
  protected array $policies = [
    User::class => UserPolicy::class,
    Client::class => ClientPolicy::class,
    Appointment::class => AppointmentPolicy::class,
    Service::class => ServicePolicy::class,
  ];

  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
      $isOwnProfile = auth()->check() && request()->route('user')?->is(auth()->user());

      $event->menu->add(['header' => 'main_navigation']);

      $event->menu->add([
        'text' => 'clients',
        'route' => 'clients.index',
        'icon' => 'fas fa-fw fa-user-friends',
        'active' => ['clients', 'clients*']
      ]);

      $event->menu->add([
        'text' => 'employees',
        'route' => 'users.index',
        'icon' => 'fas fa-fw fa-id-badge',
        'active' => $isOwnProfile ? [] : ['users', 'users/*'],
        'can' => 'viewAny',
        'model' => User::class,
      ]);

      $event->menu->add([
        'text' => 'services',
        'route' => 'services.index',
        'icon' => 'fas fa-fw fa-clipboard-list',
        'active' => ['services', 'services*']
      ]);

      $event->menu->add(['header' => 'Configurações da Conta']);

      $event->menu->add([
        'text' => 'profile',
        'route' => ['users.show', ['user' => auth()->user()]],
        'icon' => 'fas fa-fw fa-user',
        'active' => $isOwnProfile ? ['users/' . auth()->user()->uuid, 'users/' . auth()->user()->uuid . '/*'] : [],
      ]);

      $event->menu->add([
        'text' => 'password',
        'route' => 'password.request',
        'icon' => 'fas fa-fw fa-cog',
      ]);
    });
  }
}
