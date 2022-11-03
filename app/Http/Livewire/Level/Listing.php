<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    private $levels;
    public $search = '';
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteLevel'  => 'delete',
        'levelCreated' => '$refresh',
        'levelUpdated' => '$refresh',
        'levelDeleted' => '$refresh',
        'prizeCreated' => '$refresh',
        'prizeUpdated' => '$refresh',
    ];


    public function delete(Level $level)
    {
        if ($level->prize)
            $level->prize->delete();
        $level->delete();
        $this->emitSelf('levelDeleted');
    }

    public function render()
    {
        return view('livewire.level.listing', [
            'levels' => $this->levels ?? Level::with('prize')->paginate(10, ['*'], 'listing')
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
