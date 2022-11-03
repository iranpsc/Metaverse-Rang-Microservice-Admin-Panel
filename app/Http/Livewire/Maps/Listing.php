<?php

namespace App\Http\Livewire\Maps;

use App\Helpers\Feature;
use App\Models\MapManagement\Polygon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    protected $messages = [
        'name.required' => 'نام آبادی را وارد کنید',
        'name.string' => 'نام صحیح نیست',
        'name.min' => 'طول نام حداقل 2 کاراکتر است',
        'file.required' => 'فایل را انتخاب کنید',
        'file.mimes' => 'فرمت فایل صحیح نمی باشد',
    ];

    protected $listeners = [
        'deletePolygon' => 'delete',
        'polygonDeleted' => '$refresh',
        'mapsInsertedToDatabase' => '$refresh'
    ];

    protected $paginationTheme = 'bootstrap';

    public function save()
    {
        $this->validate();

        $fileName =  $this->file->getClientOriginalName();

        $this->file->storePubliclyAs('public/maps', $fileName);

        $fileContents = file_get_contents(public_path('storage/maps/' . $fileName));

        $fileContents = explode('=', $fileContents)[1];

        $fileContents = json_decode($fileContents, true);

        $polygon_count = count($fileContents['features']);

        $polygons_total_area = 0;

        foreach ($fileContents['features'] as $feature)
        {
            $polygons_total_area += ($feature['properties']['area'] * $feature['properties']['density']);
        }

        $first_id = $fileContents['features'][0]['properties']['id'];
        $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? "";
        $karbari = Feature::getKarbari($fileContents['features'][0]['properties']['karbari']);

        $polygon = new Polygon();
        $polygon->name = $this->name;
        $polygon->publish_date = Jalalian::forge(now())->format('Y/m/d');
        $polygon->publisher_name = Auth::user()->name;
        $polygon->polygon_count = $polygon_count;
        $polygon->total_area = $polygons_total_area;
        $polygon->first_id = $first_id;
        $polygon->last_id = $last_id;
        $polygon->status = 0;
        $polygon->karbari = $karbari;
        $polygon->fileName = $fileName;
        $polygon->save();
        $this->reset(['name', 'file']);
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete(Polygon $polygon)
    {
        unlink(public_path('storage/maps/' . $polygon->fileName));
        $polygon->delete();
        $this->emitSelf('polygonDeleted');
    }

    public function render()
    {
        return view('livewire.maps.listing', [
            'polygons' => Polygon::paginate(10, ['*'], 'listing')
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
