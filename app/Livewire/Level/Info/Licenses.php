<?php

namespace App\Livewire\Level\Info;

use App\Models\Level\Level;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Licenses extends Component
{
    use SendsVerificationSms;

    public Level $level;

    public $licenses,
        $create_union,
        $add_memeber_to_union,
        $observation_license,
        $gate_license,
        $lawyer_license,
        $city_counsile_entry,
        $establish_special_residential_property,
        $establish_property_on_surface,
        $judge_entry,
        $upload_image,
        $delete_image,
        $inter_level_general_points,
        $inter_level_special_points,
        $rent_out_satisfaction,
        $access_to_answer_questions_unit,
        $create_challenge_questions,
        $upload_music;

    public function mount()
    {
        $licenses = $this->level->licenses;
        $this->licenses = $licenses;

        $this->create_union = $licenses ? $licenses->create_union : false;
        $this->add_memeber_to_union = $licenses ? $licenses->add_memeber_to_union :  false;
        $this->observation_license = $licenses ? $licenses->observation_license : false;
        $this->gate_license = $licenses ? $licenses->gate_license : false;
        $this->lawyer_license = $licenses ? $licenses->lawyer_license :  false;
        $this->city_counsile_entry = $licenses ? $licenses->city_counsile_entry :  false;
        $this->establish_special_residential_property = $licenses ? $licenses->establish_special_residential_property :  false;
        $this->establish_property_on_surface = $licenses ? $licenses->establish_property_on_surface : false;
        $this->judge_entry = $licenses ? $licenses->judge_entry :  false;
        $this->upload_image = $licenses ? $licenses->upload_image : false;
        $this->delete_image = $licenses ? $licenses->delete_image :  false;
        $this->inter_level_general_points = $licenses ? $licenses->inter_level_general_points :  false;
        $this->inter_level_special_points = $licenses ? $licenses->inter_level_special_points :  false;
        $this->rent_out_satisfaction = $licenses ? $licenses->rent_out_satisfaction :  false;
        $this->access_to_answer_questions_unit = $licenses ? $licenses->access_to_answer_questions_unit :  false;
        $this->create_challenge_questions = $licenses ? $licenses->create_challenge_questions :  false;
        $this->upload_music = $licenses ? $licenses->upload_music :  false;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'create_union' => 'required|boolean',
        'add_memeber_to_union' => 'required|boolean',
        'observation_license' => 'required|boolean',
        'gate_license' => 'required|boolean',
        'lawyer_license' => 'required|boolean',
        'city_counsile_entry' => 'required|boolean',
        'establish_special_residential_property' => 'required|boolean',
        'establish_property_on_surface' => 'required|boolean',
        'judge_entry' => 'required|boolean',
        'upload_image' => 'required|boolean',
        'delete_image' => 'required|boolean',
        'inter_level_general_points' => 'required|boolean',
        'inter_level_special_points' => 'required|boolean',
        'rent_out_satisfaction' => 'required|boolean',
        'access_to_answer_questions_unit' => 'required|boolean',
        'create_challenge_questions' => 'required|boolean',
        'upload_music' => 'required|boolean',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $data = $this->validate();

        unset($data['phone_verification']);
        unset($data['access_password']);

        if ($this->licenses) {
            $this->licenses->update($data);
        } else {
            $this->licenses = $this->level->licenses()->create($data);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}
