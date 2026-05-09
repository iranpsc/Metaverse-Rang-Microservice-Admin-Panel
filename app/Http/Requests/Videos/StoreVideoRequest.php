<?php

namespace App\Http\Requests\Videos;

use App\Models\VideoSubCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'video_category_id' => ['required', 'integer', 'exists:video_categories,id'],
            'video_sub_category_id' => ['required', 'integer', 'exists:video_sub_categories,id'],
            'image' => ['required', 'image', 'max:1024'],
            'video_file_name' => ['required', 'string'],
            'creator_code' => ['required', 'string', 'exists:users,code'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $categoryId = (int) $this->input('video_category_id');
            $subCategoryId = (int) $this->input('video_sub_category_id');

            if ($categoryId && $subCategoryId) {
                $belongsToCategory = VideoSubCategory::whereKey($subCategoryId)
                    ->where('video_category_id', $categoryId)
                    ->exists();

                if (! $belongsToCategory) {
                    $validator->errors()->add('video_sub_category_id', 'زیر دسته انتخاب شده با دسته اصلی همخوانی ندارد.');
                }
            }
        });
    }
}
