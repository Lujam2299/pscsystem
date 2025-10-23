<?php

namespace App\Livewire;

use Livewire\Component;

class RegistrosNominasTabs extends Component
{
    public $activeTab = 'quincenal';

    public function switchTo($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.registros-nominas-tabs');
    }
}
