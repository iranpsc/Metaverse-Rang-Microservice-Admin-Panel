<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelLicenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'create_union' => (bool) $this->create_union,
            'add_memeber_to_union' => (bool) $this->add_memeber_to_union,
            'observation_license' => (bool) $this->observation_license,
            'gate_license' => (bool) $this->gate_license,
            'lawyer_license' => (bool) $this->lawyer_license,
            'city_counsile_entry' => (bool) $this->city_counsile_entry,
            'establish_special_residential_property' => (bool) $this->establish_special_residential_property,
            'establish_property_on_surface' => (bool) $this->establish_property_on_surface,
            'judge_entry' => (bool) $this->judge_entry,
            'upload_image' => (bool) $this->upload_image,
            'delete_image' => (bool) $this->delete_image,
            'inter_level_general_points' => (bool) $this->inter_level_general_points,
            'inter_level_special_points' => (bool) $this->inter_level_special_points,
            'rent_out_satisfaction' => (bool) $this->rent_out_satisfaction,
            'access_to_answer_questions_unit' => (bool) $this->access_to_answer_questions_unit,
            'create_challenge_questions' => (bool) $this->create_challenge_questions,
            'upload_music' => (bool) $this->upload_music,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}


