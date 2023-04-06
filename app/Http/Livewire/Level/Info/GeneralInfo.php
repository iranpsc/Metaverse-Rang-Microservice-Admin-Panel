<?php

namespace App\Http\Livewire\Level\Info;

use Livewire\Component;

class GeneralInfo extends Component
{
    public $level, $generalInfo, $score, $description, $rank,
        $subcategories, $creation_date, $persian_font,
        $english_font, $file_volume, $used_colors, $points, $designer, $model_designer;

    public function mount()
    {
        $generalInfo = $this->level->generalInfo;
        $this->generalInfo = $generalInfo;

        $this->score = $generalInfo ? $generalInfo->score : 0;
        $this->description = $generalInfo ? $generalInfo->description : '';
        $this->rank = $generalInfo ? $generalInfo->rank : 0;
        $this->subcategories = $generalInfo ? $generalInfo->subcategories : 0;
        $this->creation_date = $generalInfo ? $generalInfo->creation_date : '';
        $this->persian_font = $generalInfo ? $generalInfo->persian_font : '';
        $this->english_font = $generalInfo ? $generalInfo->english_font : '';
        $this->file_volume = $generalInfo ? $generalInfo->file_volume : 0;
        $this->used_colors = $generalInfo ? $generalInfo->used_colors : '';
        $this->points = $generalInfo ? $generalInfo->points : 0;
        $this->designer = $generalInfo ? $generalInfo->designer : '';
        $this->model_designer = $generalInfo ? $generalInfo->model_designer : '';
    }

    protected $rules = [
        'score' => 'required|integer|min:0',
        'description' => 'required|string|max:2000',
        'rank' => 'required|integer|min:0',
        'subcategories' => 'required|integer|min:0',
        'persian_font' => 'required|string|max:255',
        'english_font' => 'required|string|max:255',
        'file_volume' => 'required|integer|min:0',
        'used_colors' => 'required|string|max:500',
        'points' => 'required|integer|min:0',
        'designer' => 'required|string|max:255',
        'model_designer' => 'required|string|max:255',
        'creation_date' => 'required|shamsi_date',
    ];

    public function save()
    {
        $data = $this->validate();

        if ($this->generalInfo) {
            $this->generalInfo->update($data);
        } else {
           $this->generalInfo = $this->level->generalInfo()->create($data);
        }
        session()->flash('success', 'اطلاعات با موفقیت ثبت شد.');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.level.info.general-info');
    }
}
