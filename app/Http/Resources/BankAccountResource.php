<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'shaba_num' => $this->shaba_num,
            'card_num' => $this->card_num,
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'errors' => $this->errors,
            'created_at' => $this->created_at ? jdate($this->created_at)->format('Y/m/d') : null,
            'bankable' => $this->whenLoaded('bankable', function () {
                $bankable = $this->bankable;
                if (!$bankable) {
                    return null;
                }

                $result = [
                    'id' => $bankable->id,
                    'name' => $bankable->name ?? null,
                ];

                // Check if kyc relationship is loaded and available
                if ($bankable->relationLoaded('kyc') && $bankable->kyc) {
                    $result['fname'] = $bankable->kyc->fname ?? null;
                    $result['lname'] = $bankable->kyc->lname ?? null;
                } elseif (method_exists($bankable, 'kyc')) {
                    // Fallback: try to load if method exists (but relationship not loaded)
                    $kyc = $bankable->kyc;
                    if ($kyc) {
                        $result['fname'] = $kyc->fname ?? null;
                        $result['lname'] = $kyc->lname ?? null;
                    }
                }

                return $result;
            }),
        ];
    }
}

