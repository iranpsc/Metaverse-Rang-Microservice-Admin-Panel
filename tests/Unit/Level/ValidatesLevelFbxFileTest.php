<?php

namespace Tests\Unit\Level;

use App\Http\Requests\Level\Concerns\ValidatesLevelFbxFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidatesLevelFbxFileTest extends TestCase
{
    public function test_validate_fbx_file_map_rejects_non_array_entries(): void
    {
        $errors = $this->validate(['png' => 'not-an-entry']);

        $this->assertArrayHasKey('fbx_file.png', $errors);
    }

    public function test_validate_fbx_file_map_rejects_unknown_entry_types(): void
    {
        $errors = $this->validate([
            'png' => ['type' => 'exe', 'url' => 'https://cdn.example/model.png'],
        ]);

        $this->assertArrayHasKey('fbx_file', $errors);
    }

    public function test_validate_fbx_file_map_rejects_missing_or_disallowed_extensions(): void
    {
        $this->assertArrayHasKey('fbx_file', $this->validate([
            'png' => ['type' => 'png', 'url' => 'https://cdn.example/model'],
        ]));
        $this->assertArrayHasKey('fbx_file', $this->validate([
            'png' => ['type' => 'png', 'url' => 'https://cdn.example/model.exe'],
        ]));
    }

    public function test_validate_fbx_file_map_rejects_key_and_entry_type_mismatches(): void
    {
        $this->assertArrayHasKey('fbx_file', $this->validate([
            'png' => ['type' => 'png', 'url' => 'https://cdn.example/model.fbx'],
        ]));
        $this->assertArrayHasKey('fbx_file', $this->validate([
            'png' => ['type' => 'fbx', 'url' => 'https://cdn.example/model.png'],
        ]));
    }

    public function test_validate_fbx_file_map_accepts_jpeg_family_and_query_strings(): void
    {
        $errors = $this->validate([
            'jpeg_2' => [
                'type' => 'jpg',
                'url' => 'https://cdn.example/model.jpg?token=abc',
            ],
        ]);

        $this->assertSame([], $errors);
    }

    /** @param array<string, mixed> $map */
    private function validate(array $map): array
    {
        $request = new class extends FormRequest
        {
            use ValidatesLevelFbxFile;

            public function rules(): array
            {
                return [];
            }

            public function validateMap(array $map): array
            {
                $this->merge(['fbx_file' => $map]);
                $validator = Validator::make([], []);
                $this->validateFbxFileMap($validator);

                return $validator->errors()->toArray();
            }
        };

        return $request->validateMap($map);
    }
}
