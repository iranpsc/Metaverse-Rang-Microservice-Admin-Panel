<?php

namespace App\Livewire\Maps;

use App\Models\Map;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Listing extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    public $name, $map_file, $color, $pointFile, $border_file;

    protected $rules = [
        'name' => 'required|string|min:2',
        'map_file' => 'required|file|max:10240',
        'pointFile' => 'required|file|max:10240',
        'border_file' => 'required|file|max:10240',
        'color' => 'required|string|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }


    public function save()
    {
        $this->validate();

        $mapFileName =  $this->map_file->getClientOriginalName();
        $borderFileName = $this->border_file->getClientOriginalName();
        $pointFileName = $this->pointFile->getClientOriginalName();

        $this->map_file->storePubliclyAs('maps', $mapFileName, 'public');
        $this->border_file->storePubliclyAs('maps', $borderFileName, 'public');
        $this->pointFile->storePubliclyAs('maps', $pointFileName, 'public');

        $fileContents = file_get_contents(public_path('uploads/maps/' . $mapFileName));
        $borderFileContents = file_get_contents(public_path('uploads/maps/' . $borderFileName));
        $pointFileContents = file_get_contents(public_path('uploads/maps/' . $pointFileName));

        $fileContents = explode('=', $fileContents)[1];
        $borderFileContents = explode('=', $borderFileContents)[1];
        $pointFileContents = explode('=', $pointFileContents)[1];

        $fileContents = json_decode($fileContents, true);
        $borderFileContents = json_decode($borderFileContents, true);
        $pointFileContents = json_decode($pointFileContents, true);

        $polygon_count = count($fileContents['features']);

        $polygons_total_area = 0;

        foreach ($fileContents['features'] as $feature) {
            $polygons_total_area += ($feature['properties']['area'] * $feature['properties']['density']);
        }

        $first_id = $fileContents['features'][0]['properties']['id'];
        $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? "";
        $karbari = $this->getFeatureTitle($fileContents['features'][0]['properties']['karbari']);

        $map = new Map();
        $map->name = $this->name;
        $map->publish_date = now()->format('Y/m/d');
        $map->publisher_name = auth()->guard('admin')->user()->name;
        $map->polygon_count = $polygon_count;
        $map->total_area = $polygons_total_area;
        $map->first_id = $first_id;
        $map->last_id = $last_id;
        $map->status = 0;
        $map->karbari = $karbari;
        $map->fileName = $mapFileName;
        $map->border_coordinates = json_encode($borderFileContents['features'][0]['geometry']['coordinates'][0][0]);
        $map->central_point_coordinates = json_encode($pointFileContents['features'][0]['geometry']['coordinates']);
        $map->polygon_area = intval($borderFileContents['features'][0]['properties']['area']);
        $map->polygon_address = json_encode($borderFileContents['features'][0]['properties']['address']);
        $map->polygon_color = $this->color;
        $map->save();
        $this->resetExcept('admin');
        $this->dispatch('notify', message: 'فایل با موفقیت بارگذاری شد');
    }


    public function delete(Map $map)
    {
        unlink(public_path('uploads/maps/' . $map->fileName));
        $map->delete();
    }

    protected function getFeatureTitle(string $type)
    {
        return match ($type) {
            'm' => 'مسکونی',
            't' => 'تجاری',
            'e' => 'اداری',
            'a' => 'آموزشی',
            'b' => 'بهداشتی',
            's' => 'فضای سبز',
            'f' => 'فرهنگی',
            'g' => 'گردشگری',
            'z' => 'مذهبی',
            'n' => 'نمایشگاه',
        };
    }

    #[Title('لیست نقشه ها')]
    public function render()
    {
        return view('livewire.maps.listing', [
            'maps' => Map::paginate(10)
        ]);
    }
}
