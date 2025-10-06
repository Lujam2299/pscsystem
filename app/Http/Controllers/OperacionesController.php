<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OperacionesController extends Controller
{
    public function eventualesList(){
        return view('operaciones.eventuales');
    }
}
