<?php

namespace App\Http\Livewire\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Listing extends Component
{
    use WithPagination;

    public $languages = [], $selectedLanguage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'translationAdded' => '$refresh',
        'translationDeleted' => '$refresh',
    ];

    public function mount()
    {
        $langFile = file_get_contents(public_path('lang/lang.json'));
        $this->languages = json_decode($langFile, true);
    }

    public function saveTranslation()
    {
        $this->validate([
            'selectedLanguage' => 'required|unique:sqlite.translations,code'
        ]);

        $this->selectedLanguage['dir'] = in_array($this->selectedLanguage['code'], ['ar', 'fa', 'he', 'ur']) ? 'rtl' : 'ltr';

        $translation = Translation::create([
            'code' => $this->selectedLanguage['code'],
            'name' => $this->selectedLanguage['name'],
            'native_name' => $this->selectedLanguage['nativeName'],
            'direction' => $this->selectedLanguage['dir'],
        ]);

        $modals = Modal::all()->unique('name');

        foreach ($modals as $modal) {
            $newModal = $translation->modals()->create([
                'name' => $modal->name,
            ]);

            $relatedTabs = Tab::where('modal_id', $modal->id)->get();

            foreach ($relatedTabs as $tab) {
                $newTab = $newModal->tabs()->create([
                    'name' => $tab->name,
                ]);

                $relatedFields = Field::where('tab_id', $tab->id)->get();

                foreach ($relatedFields as $field) {
                    $newTab->fields()->create([
                        'name' => $field->name,
                    ]);
                }
            }
        }

        $this->emitSelf('translationAdded');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ترجمه اضافه شد']);
        $this->reset('selectedLanguage');
    }

    public function deleteTranslation(Translation $translation)
    {
        $translation->delete();

        $this->emitSelf('translationDeleted');
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'ترجمه حذف شد']);
    }

    public function updatedSelectedLanguage()
    {
        $this->validateOnly('selectedLanguage', [
            'selectedLanguage' => 'required'
        ]);

        $this->selectedLanguage = $this->languages[$this->selectedLanguage];
    }

    public function toggleTranslationStatus(Translation $translation)
    {
        $translation->update([
            'status' => !$translation->status
        ]);

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'وضعیت ترجمه تغییر کرد']);
    }

    public function export(Translation $translation)
    {
        $translation->load('modals.tabs.fields');

        $translation->makeHidden(['id', 'created_at', 'updated_at']);
        $translation->modals->makeHidden(['id', 'translation_id']);
        $translation->modals->each->tabs->makeHidden(['id', 'modal_id']);
        $translation->modals->each->tabs->each->fields->makeHidden(['id', 'tab_id']);

        $fileName = $translation->code . '.json';
        $file = fopen(public_path('lang/' . $fileName), 'w');
        fwrite($file, json_encode($translation->toArray(), JSON_PRETTY_PRINT));
        fclose($file);

        return response()->download(public_path('lang/' . $fileName));
    }

    public function render()
    {
        return view('livewire.translations.listing', [
            'translations' => Translation::simplePaginate(10)
        ]);
    }
}
