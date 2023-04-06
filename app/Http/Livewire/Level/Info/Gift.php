<?php

namespace App\Http\Livewire\Level\Info;

use Livewire\Component;

class Gift extends Component
{
    public $level, $gift, $name, $description, $monthly_capacity_count, $store_capacity, $sell_capacity,
        $features, $sell, $vod_document_registration, $seller_link, $designer;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:2000',
        'monthly_capacity_count' => 'required|integer|min:0',
        'store_capacity' => 'required|boolean',
        'sell_capacity' => 'required|boolean',
        'features' => 'required|string|max:2000',
        'sell' => 'required|boolean',
        'vod_document_registration' => 'required|boolean',
        'seller_link' => 'required|string|max:255',
        'designer' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->gift = $this->level->gift;
        $this->name = $this->gift ? $this->gift->name : '';
        $this->description = $this->gift ? $this->gift->description : '';
        $this->monthly_capacity_count = $this->gift ? $this->gift->monthly_capacity_count : 0;
        $this->store_capacity = $this->gift ? $this->gift->store_capacity : false;
        $this->sell_capacity = $this->gift ? $this->gift->sell_capacity : false;
        $this->features = $this->gift ? $this->gift->features : '';
        $this->sell = $this->gift ? $this->gift->sell : false;
        $this->vod_document_registration = $this->gift ? $this->gift->vod_document_registration : false;
        $this->seller_link = $this->gift ? $this->gift->seller_link : '';
        $this->designer = $this->gift ? $this->gift->designer : '';
    }

    public function save()
    {
        $data = $this->validate();

        if($this->gift)
        {
            $this->gift->update($data);
        } else {
            $this->gift = $this->level->gift()->create($data);
        }

        session()->flash('success', 'اطلاعات با موفقیت ثبت شد.');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.level.info.gift');
    }
}
