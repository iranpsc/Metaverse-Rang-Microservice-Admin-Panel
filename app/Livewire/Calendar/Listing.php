<?php

namespace App\Livewire\Calendar;

use Livewire\Component;
use App\Models\Calendar;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class Listing extends Component
{
    use WithFileUploads, WithPagination, SendsVerificationSms;

    public $title, $content, $image, $start_date, $end_date, $color;
    public $btn_name, $btn_link, $start_time, $end_time;

    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2|max:5000',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2024',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'start_time' => 'required|string|min:2|max:255',
            'end_time' => 'required|string|min:2|max:255',
            'color' => 'nullable|string',
            'btn_name' => 'nullable|string|min:2|max:255',
            'btn_link' => 'nullable|string|min:2|max:255',
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
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        // Convert Jalali dates to Carbon before saving
        $startsAt = Jalalian::fromFormat('Y/m/d', $this->start_date)->toCarbon()->setTimeFromTimeString($this->start_time);
        $endsAt = Jalalian::fromFormat('Y/m/d', $this->end_date)->toCarbon()->setTimeFromTimeString($this->end_time);

        Calendar::create([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'color' => $this->color ?? '#000000',
            'writer' => $this->admin->name,
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'image' => url('uploads/' . $this->image->store('calendars', 'public')),
        ]);

        $this->resetExcept('admin');
        $this->dispatch('notify', message: 'وقعه ثبت شد');
    }

    public function delete(Calendar $calendar)
    {
        $calendar->delete();
    }

    #[Title('لیست رویدادها')]
    public function render()
    {
        return view('livewire.calendar.listing', [
            'events' => Calendar::with('interactions')
                ->where('is_version', false)->latest('starts_at')
                ->paginate(10)
        ]);
    }
}
