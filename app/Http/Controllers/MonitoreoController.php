<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deducciones;

class MonitoreoController extends Controller
{
    public function verDeducciones()
    {
        $deducciones = Deducciones::where('status', 'Pendiente')->paginate(10);
        return view('monitoreo.deducciones', compact('deducciones'));
    }

    public function mapa()
    {
        return view('monitoreo.mapa');
    }

    public function gasolinasIndex(){
        return view('monitoreo.gasolinas');
    }
}
