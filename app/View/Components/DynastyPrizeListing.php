<?php

namespace App\View\Components;

use App\Models\Dynasty\DynastyPrize;
use Illuminate\View\Component;

class DynastyPrizeListing extends Component
{
    public $prize;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(DynastyPrize $prize)
    {
        $this->prize = $prize;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dynasty-prize-listing');
    }
}
