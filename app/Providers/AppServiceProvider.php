<?php

namespace App\Providers;

use App\Http\Livewire\UsuariosLista;
use App\Models\Asistencia;
use App\Models\BuzonQueja;
use App\Models\Eventuales;
use App\Models\Finiquito;
use App\Models\Incapacidad;
use App\Models\Message;
use App\Models\RiesgoTrabajo;
use App\Models\SolicitudBajas;
use App\Models\User;
use App\Models\ValesComida;
use App\Observers\AsistenciaObserver;
use App\Observers\BuzonQuejaObserver;
use App\Observers\EventualesObserver;
use App\Observers\FiniquitoObserver;
use App\Observers\IncapacidadObserver;
use App\Observers\MessageObserver;
use App\Observers\RiesgoTrabajoObserver;
use App\Observers\SolicitudBajasObserver;
use App\Observers\ValesComidaObserver;
use App\Policies\SolicitudBajasPolicy;
use App\Policies\UserPolicy;
use App\Support\Authorization\Permission;
use App\Support\Authorization\RolePermissionMap;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
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
        foreach (Permission::all() as $permission) {
            Gate::define($permission, fn ($user) => RolePermissionMap::allows($user, $permission));
        }

        Gate::policy(SolicitudBajas::class, SolicitudBajasPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::define('view-traccar-monitoring', function ($user) {
            $rol = Str::lower(Str::ascii(trim((string) ($user->rol ?? ''))));

            return Str::contains($rol, ['admin', 'monitor']);
        });
        Gate::define('manage-traccar-monitoring', function ($user) {
            $rol = Str::lower(Str::ascii(trim((string) ($user->rol ?? ''))));

            return Str::contains($rol, ['admin']);
        });

        Message::observe(MessageObserver::class);
        BuzonQueja::observe(BuzonQuejaObserver::class);
        Incapacidad::observe(IncapacidadObserver::class);
        RiesgoTrabajo::observe(RiesgoTrabajoObserver::class);
        Asistencia::observe(AsistenciaObserver::class);
        ValesComida::observe(ValesComidaObserver::class);
        Eventuales::observe(EventualesObserver::class);
        SolicitudBajas::observe(SolicitudBajasObserver::class);
        Finiquito::observe(FiniquitoObserver::class);

        Livewire::component('usuarios-lista', UsuariosLista::class);
    }
}
