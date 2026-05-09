<?php

namespace App\Livewire\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Listing extends Component
{
    use WithPagination;

    public $languages = [], $selectedLanguage;

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

        $this->dispatch('notify', message: 'ترجمه اضافه شد');
        $this->reset('selectedLanguage');
    }

    public function delete(Translation $translation)
    {
        $translation->delete();
        $this->dispatch('notify', message: 'ترجمه حذف شد');
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

        $this->dispatch('notify', message: 'وضعیت ترجمه تغییر کرد');
    }

    public function export(Translation $translation)
    {
        $translation->load('modals.tabs.fields');

        $translation->makeHidden(['id', 'created_at', 'updated_at']);
        $translation->modals->makeHidden(['id', 'translation_id']);
        $translation->modals->each->tabs->makeHidden(['id', 'modal_id']);

        // Exclude id, tab_id, and name from fields
        $translation->modals->each(function($modal) {
            $modal->tabs->each(function($tab) {
                $tab->fields->each(function($field) {
                    $field->makeHidden(['id', 'tab_id', 'name']);
                });
            });
        });

        $fileName = $translation->code . '.json';
        $filePath = public_path('lang/' . $fileName);
        $content = json_encode($translation->toArray(), JSON_PRETTY_PRINT);

        file_put_contents($filePath, $content);

        $translation->increment('version');

        $fileName = strtolower($translation->code) . '.json';
        $fileUrl = 'https://rgb.irpsc.com/lang/' . $fileName;

        $translation->update(['file_url' => $fileUrl]);

        $content = json_decode($content, true);
        $content['file_url'] = $fileUrl;
        $content = json_encode($content, JSON_PRETTY_PRINT);

        file_put_contents($filePath, $content);

        if (app()->environment('local')) {
            return response()->download(public_path('lang/' . $fileName), $fileName);
        } else {
            try {
                if (!Storage::disk('ftp')->put('lang/' . $fileName, $content)) {
                    throw new \Exception('خطا در ذخیره فایل');
                }
                $this->dispatch('notify', message: 'فایل ذخیره شد');
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: $e->getMessage());
            }
        }
    }

    #[Title('مدیریت ترجمه ها')]
    public function render()
    {
        return view('livewire.translations.listing', [
            'translations' => Translation::paginate(10)
        ]);
    }
}
