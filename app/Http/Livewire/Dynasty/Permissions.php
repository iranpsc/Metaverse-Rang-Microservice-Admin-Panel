<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\Permission;
use Livewire\Component;

class Permissions extends Component
{
    public $permissions, $BFR, $SF, $W, $JU, $DM, $PIUP, $PITC, $PIC, $ESOO, $COTB;

    protected $listeners = ['permissionsUpdated' => '$refresh'];

    public function mount()
    {
        $this->permissions = Permission::first();
        $this->BFR  = $this->permissions->BFR  ? $this->permissions->BFR  : 0;
        $this->SF   = $this->permissions->SF   ? $this->permissions->SF   : 0;
        $this->W    = $this->permissions->W    ? $this->permissions->W    : 0;
        $this->JU   = $this->permissions->JU   ? $this->permissions->JU   : 0;
        $this->DM   = $this->permissions->DM   ? $this->permissions->DM   : 0;
        $this->PIUP = $this->permissions->PIUP ? $this->permissions->PIUP : 0;
        $this->PITC = $this->permissions->PITC ? $this->permissions->PITC : 0;
        $this->PIC  = $this->permissions->PIC  ? $this->permissions->PIC  : 0;
        $this->ESOO = $this->permissions->ESOO ? $this->permissions->ESOO : 0;
        $this->COTB = $this->permissions->COTB ? $this->permissions->COTB : 0;
    }

    public function update()
    {
        Permission::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'BFR' => $this->BFR,
                'SF' => $this->SF,
                'W' => $this->W,
                'JU' => $this->JU,
                'DM' => $this->DM,
                'PIUP' => $this->PIUP,
                'PITC' => $this->PITC,
                'PIC' => $this->PIC,
                'ESOO' => $this->ESOO,
                'COTB' => $this->COTB
            ]
        );
        session()->flash('success', 'دسترسی ها بروزرسانی شدند');
        $this->emitSelf('permissionsUpdated');
    }

    public function render()
    {
        return view('livewire.dynasty.permissions');
    }
}
