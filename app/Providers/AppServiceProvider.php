<?php

namespace App\Providers;

use App\Models\Asistencia;
use App\Models\BuzonQueja;
use App\Models\Incapacidad;
use App\Models\Message;
use App\Models\RiesgoTrabajo;
use App\Observers\AsistenciaObserver;
use App\Observers\BuzonQuejaObserver;
use App\Observers\IncapacidadObserver;
use App\Observers\MessageObserver;
use App\Observers\RiesgoTrabajoObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Http\Livewire\UsuariosLista;

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
        Message::observe(MessageObserver::class);
        BuzonQueja::observe(BuzonQuejaObserver::class);
        Incapacidad::observe(IncapacidadObserver::class);
        RiesgoTrabajo::observe(RiesgoTrabajoObserver::class);
        Asistencia::observe(AsistenciaObserver::class);

        Livewire::component('usuarios-lista', UsuariosLista::class);
    }
}
