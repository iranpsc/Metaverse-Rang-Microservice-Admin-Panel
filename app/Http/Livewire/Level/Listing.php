<?php

namespace App\Http\Livewire\Level;

use Livewire\Component;
use App\Models\Level\Level;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

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
        $level->prize?->delete();

        if ($level->image) {
            unlink(public_path('uploads/' . $level->image->url));
            $level->image->delete();
        }

        $level->delete();
        $this->emitSelf('levelDeleted');
    }

    public function render()
    {
        return view('livewire.level.listing', [
            'levels' => Level::with(['prize', 'image', 'generalInfo'])->simplePaginate(10)
        ]);
    }
}
