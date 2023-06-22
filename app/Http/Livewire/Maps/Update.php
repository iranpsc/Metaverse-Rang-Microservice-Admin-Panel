<?php

namespace App\Http\Livewire\Maps;

use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $name, $color, $pointFile, $borderFile, $map;

    protected $rules = [
        'name' => 'required|string|min:2',
        'pointFile' => 'required|file|max:10240',
        'borderFile' => 'required|file|max:10240',
        'color' => 'required|string|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
        $this->name = $this->map->name;
        $this->color = $this->map->color;
    }

    public function save()
    {
        $this->validate();

        $borderFileName = $this->borderFile->getClientOriginalName();
        $pointFileName = $this->pointFile->getClientOriginalName();

        $this->borderFile->storePubliclyAs('maps', $borderFileName, 'public');
        $this->pointFile->storePubliclyAs('maps', $pointFileName, 'public');

        $borderFileContents = file_get_contents(public_path('uploads/maps/' . $borderFileName));
        $pointFileContents = file_get_contents(public_path('uploads/maps/' . $pointFileName));

        $borderFileContents = explode('=', $borderFileContents)[1];
        $pointFileContents = explode('=', $pointFileContents)[1];

        $borderFileContents = json_decode($borderFileContents, true);
        $pointFileContents = json_decode($pointFileContents, true);

        $this->map->update([
            'name' => $this->name,
            'polygon_color' => $this->color,
            'border_coordinates' => json_encode($borderFileContents['features'][0]['geometry']['coordinates'][0][0]),
            'central_point_coordinates' => json_encode($pointFileContents['features'][0]['geometry']['coordinates']),
            'polygon_area' => intval($borderFileContents['features'][0]['properties']['area']),
            'polygon_address' => json_encode($borderFileContents['features'][0]['properties']['address'])
        ]);

        $this->emitUp('mapUpdated');
        $this->reset(['phone_verification', 'access_password']);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'Map updated successfully!']);
    }

    public function render()
    {
        return view('livewire.maps.update');
    }
}
