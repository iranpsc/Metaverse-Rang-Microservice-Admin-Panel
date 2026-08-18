<?php

namespace App\Livewire;

use App\Models\Calendar;
use App\Traits\SendsVerificationSms;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Versions extends Component
{
    use WithPagination, SendsVerificationSms;

    public $title, $content, $versionTitle, $startsAt;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:20000',
            'versionTitle' => 'required|string|max:255',
            'startsAt' => 'required|date',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ],
        ];
    }

    public function mount()
    {
        $this->admin = auth()->user();
    }

    public function save()
    {
        $this->validate();

        Calendar::create([
            'title' => $this->title,
            'content' => $this->content,
            'is_version' => true,
            'version_title' => $this->versionTitle,
            'starts_at' => $this->startsAt,
            'writer' => $this->admin->name,
        ]);

        $this->dispatch('notify', message: 'ورژن جدید با موفقیت ایجاد شد.');

        $this->resetExcept('admin');
    }

    public function delete(Calendar $version)
    {
        $version->delete();
    }

    #[Title('ورژن‌ها')]
    public function render()
    {
        return view('livewire.versions', [
            'versions' => Calendar::version()
                ->orderByDesc('id')
                ->paginate('10')
        ]);
    }
}
