<?php

namespace App\Livewire\Maps;

use App\Models\Map;
use App\Traits\SendsVerificationSms;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class Listing extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    public $name, $map_file, $color, $point_file, $border_file;

    /**
     * Get the validation rules for the form fields.
     *
     * @return array The validation rules.
     */
    protected function rules()
    {
        return [
            'name' => 'required|string|min:2',
            'map_file' => 'required|file|max:10240',
            'point_file' => 'required|file|max:10240',
            'border_file' => 'required|file|max:10240',
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

    /**
     * Mount the component.
     *
     * This method is called when the component is being mounted.
     * It sets the authenticated admin user.
     *
     * @return void
     */
    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }

    /**
     * Save the form data.
     *
     * This method is called when the form is submitted.
     * It validates the form fields and saves the map data to the database.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        // Get the original file names
        $mapFileName =  $this->map_file->getClientOriginalName();
        $borderFileName = $this->border_file->getClientOriginalName();
        $pointFileName = $this->point_file->getClientOriginalName();

        // Store the files in the public storage
        $this->map_file->storePubliclyAs('maps', $mapFileName, 'public');
        $this->border_file->storePubliclyAs('maps', $borderFileName, 'public');
        $this->point_file->storePubliclyAs('maps', $pointFileName, 'public');

        // Read the file contents
        $fileContents = file_get_contents(public_path('uploads/maps/' . $mapFileName));
        $borderFileContents = file_get_contents(public_path('uploads/maps/' . $borderFileName));
        $pointFileContents = file_get_contents(public_path('uploads/maps/' . $pointFileName));

        // Extract the relevant data from the file contents
        $fileContents = explode('=', $fileContents)[1];
        $borderFileContents = explode('=', $borderFileContents)[1];
        $pointFileContents = explode('=', $pointFileContents)[1];

        // Decode the JSON data
        $fileContents = json_decode($fileContents, true);
        $borderFileContents = json_decode($borderFileContents, true);
        $pointFileContents = json_decode($pointFileContents, true);

        // Calculate the polygon count and total area
        $polygon_count = count($fileContents['features']);
        $polygons_total_area = 0;

        foreach ($fileContents['features'] as $feature) {
            $polygons_total_area += ($feature['properties']['area'] * $feature['properties']['density']);
        }

        // Get the first and last IDs, and the karbari title
        $first_id = $fileContents['features'][0]['properties']['id'];
        $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? "";
        $karbari = $this->getFeatureTitle($fileContents['features'][0]['properties']['karbari']);

        // Create a new Map instance and save it to the database
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

        // Reset the form fields
        $this->resetExcept('admin');

        // Dispatch a notification event
        $this->dispatch('notify', message: 'فایل با موفقیت بارگذاری شد');
    }

    /**
     * Delete a map.
     *
     * This method is called when a map needs to be deleted.
     * It deletes the map file from the storage and removes the map from the database.
     *
     * @param Map $map The map to delete.
     * @return void
     */
    public function delete(Map $map)
    {
        unlink(public_path('uploads/maps/' . $map->fileName));
        $map->delete();
    }

    /**
     * Get the title for a feature type.
     *
     * This method returns the title for a given feature type.
     *
     * @param string $type The feature type.
     * @return string The title for the feature type.
     */
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
