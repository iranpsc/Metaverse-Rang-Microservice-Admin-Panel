<?php

namespace App\Http\Livewire\Level;

use App\Models\Level\Prize;
use Livewire\Component;

class CreatePrize extends Component
{
    public $level, $key, $prize;
    public $psc, $blue, $red, $yellow, $union_license, $union_members_count;
    public $observing_license, $gate_license, $lawyer_license, $city_counsile_entry;
    public $special_residence_property, $property_on_area, $judge_entry, $satisfaction, $upload_feature_image;
    public $delete_feature_image, $effect;

    protected $rules = [
        'psc' => 'required|integer|min:0',
        'blue' => 'required|integer|min:0',
        'red' => 'required|integer|min:0',
        'yellow' => 'required|integer|min:0',
        'union_members_count' => 'required|integer|min:0',
        'special_residence_property' => 'required|integer|min:0',
        'property_on_area' => 'required|integer|min:0',
        'satisfaction' => 'required|numeric',
        'effect' => 'required|integer',
    ];

    protected $messages = [
        'property_on_area.integer' => 'مقدار عددی وارد کنید',
        'property_on_area.min' => 'کمترین مقدار 0 می باشد',
        'property_on_area.required' => 'مقدار فیلد را تعیین کنید',
        'psc.integer' => 'مقدار عددی وارد کنید',
        'psc.min' => 'کمترین مقدار 0 می باشد',
        'psc.required' => 'مقدار فیلد را تعیین کنید',
        'blue.integer' => 'مقدار عددی وارد کنید',
        'blue.min' => 'کمترین مقدار 0 می باشد',
        'blue.required' => 'مقدار فیلد را تعیین کنید',
        'red.integer' => 'مقدار عددی وارد کنید',
        'red.min' => 'کمترین مقدار 0 می باشد',
        'red.required' => 'مقدار فیلد را تعیین کنید',
        'yellow.integer' => 'مقدار عددی وارد کنید',
        'yellow.min' => 'کمترین مقدار 0 می باشد',
        'yellow.required' => 'مقدار فیلد را تعیین کنید',
        'special_residence_property.integer' => 'مقدار عددی وارد کنید',
        'special_residence_property.min' => 'کمترین مقدار 0 می باشد',
        'special_residence_property.required' => 'مقدار فیلد را تعیین کنید',
        'union_members_count.integer' => 'مقدار عددی وارد کنید',
        'union_members_count.min' => 'کمترین مقدار 0 می باشد',
        'union_members_count.required' => 'مقدار فیلد را تعیین کنید',
        'satisfaction.required' => 'مقدار رضایت را وارد کنید',
        'satisfaction.numeric' => 'لطفا مقدار عددی وارد کنید',
        'satisfaction.min' => 'کمترین مقدار 0 می باشد',
        'effect.required' => 'حد تاثیر را وارد کنید',
        'effect.integer' => 'لطفا مقدار حد تاثیر را عددی وارد کنید',
        'effect.min' => 'کمترین مقدار 0 می باشد',
    ];

    public function save() {
        $this->validate();
        if($this->level->prize) {
            session()->flash('error', 'جوایز این سطح قبلا تعریف شده است');
            return;
        }

        $this->prize = new Prize();
        $this->prize->level_id = $this->level->id;
        $this->prize->psc = $this->psc;
        $this->prize->blue = $this->blue;
        $this->prize->red = $this->red;
        $this->prize->yellow = $this->yellow;
        $this->prize->union_license = $this->union_license ?? 0;
        $this->prize->union_members_count = $this->union_members_count;
        $this->prize->observing_license = $this->observing_license ?? 0;
        $this->prize->gate_license = $this->gate_license ?? 0;
        $this->prize->lawyer_license = $this->lawyer_license ?? 0;
        $this->prize->city_counsil_entry = $this->city_counsile_entry ?? 0;
        $this->prize->special_residence_property = $this->special_residence_property;
        $this->prize->property_on_area = $this->property_on_area;
        $this->prize->judge_entry = $this->judge_entry ?? 0;
        $this->prize->satisfaction = $this->satisfaction;
        $this->prize->effect = $this->effect;
        $this->prize->upload_feature_image = $this->upload_feature_image ?? 0; 
        $this->prize->delete_feature_image = $this->delete_feature_image ?? 0;
        $this->prize->save();
        session()->flash('success', 'جوایز سطح با موفقیت ثبت شد');
        $this->emitUp('prizeCreated');
    }

    public function updated($prop) {
        $this->validateOnly($prop);
    }


    public function render()
    {
        return view('livewire.level.create-prize');
    }
}
