<?php

namespace App\Http\Livewire\Statistics;

use App\Models\StatisticesSettings;
use App\Models\StatisticesTypes;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Statistics extends Component
{

    public $statisticType, $statistic = [];

    public function update()
    {
        $statisticTypeObj = StatisticesTypes::where('key', $this->statisticType)->first();

        foreach ($this->statistic as $item) {
            $statisticSetting =  StatisticesSettings::where('key', $item)->first();

                if($statisticSetting->StatisticesTypesSettings()
                ->where('statistices_settings_id' , $statisticSetting->id )
                ->where('statistices_types_id' ,$statisticTypeObj->id )->exists())
                {

                $this->updatePivot($statisticTypeObj, $statisticSetting);


                session()->flash('success', 'تغییرات اعمال گردید');
                }else{
                    $statisticSetting->StatisticesTypesSettings()->attach($statisticTypeObj, [
                        'status' => 1,
                    ]);
                    session()->flash('success', 'تغییرات اعمال گردید');

                }

        }

        $this->reset('statisticType','statistic');
    }


    private function updatePivot($statisticTypeObj,$statisticSetting)
    {
        $status = DB::table('statistices_types_settings')->where('statistices_settings_id', $statisticSetting->id)
        ->where('statistices_types_id', $statisticTypeObj->id)->pluck('status')->first();


    $setting = DB::table('statistices_types_settings')->where('statistices_settings_id', $statisticSetting->id)
        ->where('statistices_types_id', $statisticTypeObj->id)->update([
            'status' => $status == 1 ? 0 : 1
        ]);
    }

    public function render()
    {
        return view('livewire.statistics.statistics', [

            'statisticsSettings' => StatisticesSettings::all(),
            'statisticsTypes' => StatisticesTypes::all()
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
