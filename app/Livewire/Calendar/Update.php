<?php

namespace App\Livewire\Calendar;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class Update extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $event, $title, $content, $image, $start_date, $end_date, $color;
    public $btn_name, $btn_link, $start_time, $end_time;

    public function rules()
    {
        return [
            'title' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2|max:5000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2024',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|string|min:2|max:255',
            'end_time' => 'nullable|string|min:2|max:255',
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

    public function mount($event)
    {

        $this->title = $event->title;
        $this->content = $event->content;
        $this->color = $event->color;
        $this->btn_name = $event->btn_name;
        $this->btn_link = $event->btn_link;

        // Convert Carbon dates to Jalali for display in Persian date picker
        $this->start_date = Jalalian::fromCarbon($event->starts_at)->format('Y/m/d');
        $this->end_date = $event->is_version ? null : ($event->ends_at ? Jalalian::fromCarbon($event->ends_at)->format('Y/m/d') : null);
        $this->start_time = $event->is_version ? null : $event->starts_at->format('H:i');
        $this->end_time = $event->is_version ? null : ($event->ends_at ? $event->ends_at->format('H:i') : null);
        $this->event = $event;

        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        // Convert Jalali dates to Carbon before saving
        $startsAt = $this->event->is_version
            ? Jalalian::fromFormat('Y/m/d', $this->start_date)->toCarbon()
            : Jalalian::fromFormat('Y/m/d', $this->start_date)->toCarbon()->setTimeFromTimeString($this->start_time);

        $endsAt = $this->event->is_version
            ? null
            : ($this->end_date ? Jalalian::fromFormat('Y/m/d', $this->end_date)->toCarbon()->setTimeFromTimeString($this->end_time) : null);

        $this->event->update([
            'title' => $this->title,
            'content' => $this->content,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'color' => $this->color ?? ' ',
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'image' => $this->image ? url('uploads/' . $this->image->store('calendars', 'public')) : $this->event->image,
        ]);

        $this->dispatch('notify', message: 'وقعه ویرایش شد');
    }
    public function render()
    {
        return view('livewire.calendar.update');
    }
}
