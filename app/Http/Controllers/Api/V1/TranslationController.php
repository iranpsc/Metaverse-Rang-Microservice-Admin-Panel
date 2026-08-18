<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;

class TranslationController extends Controller
{
    public function index()
    {
        $translations = Translation::active()->get();
        return response()->json([
            'data' => $translations->map(function($translation) {
                return [
                    'id' => $translation->id,
                    'code' => $translation->code,
                    'name' => $translation->name,
                    'native_name' => $translation->native_name,
                    'direction' => $translation->direction,
                    'version' => $translation->version,
                    'icon' => asset('assets/images/flags/' . strtoupper($translation->code) . '.svg'),
                    'file_url' => $translation->file_url,
                ];
            })
        ]);
    }

    public function getModals(Translation $translation)
    {
        $modals = $translation->modals()->get();
        return response()->json(['data' => $modals]);
    }

    public function getTabs(Translation $translation, Modal $modal)
    {
        return response()->json(['data' => $modal->tabs]);
    }

    public function getFields(Translation $translation, Modal $modal, Tab $tab)
    {
        return response()->json(['data' => $tab->fields]);
    }
}
