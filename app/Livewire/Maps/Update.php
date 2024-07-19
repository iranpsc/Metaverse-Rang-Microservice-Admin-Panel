<?php

namespace App\Livewire\Maps;

use App\Models\Map;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public Map $map;

    public $name, $color, $pointFile, $borderFile;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2',
            'pointFile' => 'required|file|max:10240',
            'borderFile' => 'required|file|max:10240',
            'color' => 'required|string|max:255',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
            ]
        ];
    }

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

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ویرایش شد');
    }

    public function render()
    {
        return view('livewire.maps.update');
    }
}
