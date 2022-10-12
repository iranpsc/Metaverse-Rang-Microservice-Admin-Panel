<?php

namespace App\Http\Controllers;

// use App\Helpers\ImportMaps;
use Illuminate\Support\Facades\File;
use App\Models\Map;
use App\Jobs\ImportMaps;

class MapController extends Controller
{
    public function filesListView()
    {
        $filesInFolder = File::files(public_path('map/layers'));
        foreach ($filesInFolder as $path) {
            if (
                pathinfo($path)['filename'] == 'layers' ||
                pathinfo($path)['filename'] == 'flag_12' || pathinfo($path)['filename'] == 'border_13'
            )
                continue;
            $file_names[] = [
                'name' => pathinfo($path)['filename'],
                'status' => Map::firstWhere(['name' => pathinfo($path)['filename']])->exists ?? 0
            ];
        }
        return view('import-map', ['maps' => $file_names]);
    }
    public function readAndCreateFromJsFileUsingName()
    {
        $filesInFolder = File::files(public_path('map/layers'));
        foreach ($filesInFolder as $path) {
            if (
                pathinfo($path)['filename'] == 'layers' ||
                pathinfo($path)['filename'] == 'flag_12' || pathinfo($path)['filename'] == 'border_13'
            )
                continue;
            $maps[] =  [
                'name' => pathinfo($path)['filename']
            ];
        }
        ImportMaps::dispatchAfterResponse($maps);

        return redirect()->back()->with('success', 'اطلاعات فایل نقشه با موفقیت وارد دیتابیس شد');
    }
}
