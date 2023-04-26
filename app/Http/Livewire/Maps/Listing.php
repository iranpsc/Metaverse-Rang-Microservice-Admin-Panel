<?php

namespace App\Http\Livewire\Maps;

use App\Helpers\Feature;
use App\Models\Map;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class Listing extends Component
{
    use WithFileUploads, WithPagination;

    public $name, $file;

    protected $rules = [
        'name' => 'required|string|min:2',
        'file' => 'required|file'
    ];

    protected $listeners = [
        'deleteMap' => 'delete',
        'mapDeleted' => '$refresh',
        'mapsInsertedToDatabase' => '$refresh'
    ];

    protected $paginationTheme = 'bootstrap';

    public function save()
    {
        $this->validate();

        $fileName =  $this->file->getClientOriginalName();

        $this->file->storePubliclyAs('maps', $fileName, 'public');

        $fileContents = file_get_contents(public_path('uploads/maps/' . $fileName));

        $fileContents = explode('=', $fileContents)[1];

        $fileContents = json_decode($fileContents, true);

        $polygon_count = count($fileContents['features']);

        $polygons_total_area = 0;

        foreach ($fileContents['features'] as $feature) {
            $polygons_total_area += ($feature['properties']['area'] * $feature['properties']['density']);
        }

        $first_id = $fileContents['features'][0]['properties']['id'];
        $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? "";
        $karbari = Feature::getKarbari($fileContents['features'][0]['properties']['karbari']);

        $map = new Map();
        $map->name = $this->name;
        $map->publish_date = Jalalian::forge(now())->format('Y/m/d');
        $map->publisher_name = Auth::guard('admin')->user()->name;
        $map->polygon_count = $polygon_count;
        $map->total_area = $polygons_total_area;
        $map->first_id = $first_id;
        $map->last_id = $last_id;
        $map->status = 0;
        $map->karbari = $karbari;
        $map->fileName = $fileName;
        $map->save();
        $this->reset(['name', 'file']);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'فایل با موفقیت بارگذاری شد']);
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete(Map $map)
    {
        unlink(public_path('uploads/maps/' . $map->fileName));
        $map->delete();
        $this->emitSelf('mapDeleted');
    }

    public function render()
    {
        return view('livewire.maps.listing', [
            'maps' => Map::simplePaginate(10)
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
