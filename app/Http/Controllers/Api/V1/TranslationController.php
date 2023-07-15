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
        return response()->json(['data' => $translations]);
    }

    public function getModals(Translation $translation)
    {
        $modals = $translation->modals()->get();
        return response()->json(['data' => $modals]);
    }

    public function getTabs(Modal $modal)
    {
        return response()->json(['data' => $modal->tabs]);
    }

    public function getFields(Tab $tab)
    {
        return response()->json(['data' => $tab->fields]);
    }
}
