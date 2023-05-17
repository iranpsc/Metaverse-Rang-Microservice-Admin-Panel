<?php

namespace App\Http\Livewire\Maps;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $color, $pointFile, $borderFile, $map;

    protected $rules = [
        'pointFile' => 'required|file|max:10240',
        'borderFile' => 'required|file|max:10240',
        'color' => 'required|string|max:255',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    protected $listeners = [
        'deleteMap' => 'delete',
        'mapDeleted' => '$refresh',
        'mapsInsertedToDatabase' => '$refresh'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
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
            'border_coordinates' => json_encode($borderFileContents['features'][0]['geometry']['coordinates']),
            'central_point_coordinates' => json_encode($pointFileContents['features'][0]['geometry']['coordinates']),
            'polygon_color' => $this->color,
            'polygon_address' => json_encode($borderFileContents['features'][0]['properties']['address']),
            'polygon_area' => intval($borderFileContents['features'][0]['properties']['area'])
        ]);

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات نقشه با موفقیت بروزرسانی شد!']);

        $this->emitUp('mapUpdated');
    }
    public function render()
    {
        return view('livewire.maps.update');
    }
}
