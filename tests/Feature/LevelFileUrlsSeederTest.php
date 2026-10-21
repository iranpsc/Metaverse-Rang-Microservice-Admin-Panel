<?php

namespace Tests\Feature;

use Database\Seeders\ReplaceLevelFileUrlsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class LevelFileUrlsSeederTest extends TestCase
{
    public function test_it_replaces_file_urls_and_preserves_other_data_on_repeated_runs(): void
    {
        config(['app.url' => 'https://files.example.test/admin/']);
        $old = 'https://admin.metarang.com';
        $new = 'https://files.example.test/admin';
        $tables = [
            'levels' => ['background_image'],
            'level_gems' => ['png_file', 'fbx_file'],
            'level_gifts' => ['png_file', 'gif_file', 'fbx_file'],
            'level_general_infos' => ['png_file', 'gif_file', 'fbx_file'],
        ];
        $map = [
            'fbx' => ['url' => $old.'/uploads/model.fbx?download=1#file', 'type' => 'fbx', 'size' => '123'],
            'obj' => $old.'/uploads/model.obj',
            'external' => ['url' => 'https://cdn.example.test/model.fbx'],
            $old.'/key' => 'preserved key',
        ];

        foreach ($tables as $table => $columns) {
            Schema::create($table, function (Blueprint $schema) use ($columns) {
                $schema->id();
                foreach ($columns as $column) {
                    $schema->text($column)->nullable();
                }
                $schema->text('description');
            });

            foreach (['plain', 'escaped_json', 'unescaped_json', 'null', 'empty', 'external', 'lookalike', 'relative'] as $kind) {
                $data = ['description' => $old.'/must-stay'];
                foreach ($columns as $column) {
                    $data[$column] = match ($kind) {
                        'plain' => $old.'/uploads/file.png',
                        'escaped_json' => $column === 'fbx_file' ? json_encode($map) : $old.'/uploads/file.png',
                        'unescaped_json' => $column === 'fbx_file' ? json_encode($map, JSON_UNESCAPED_SLASHES) : $old.'/uploads/file.png',
                        'null' => null,
                        'empty' => '',
                        'external' => 'https://cdn.example.test/file.png',
                        'lookalike' => $old.'.example.test/file.png',
                        'relative' => 'uploads/file.png',
                    };
                }
                DB::table($table)->insert($data);
            }
        }

        $this->seed(ReplaceLevelFileUrlsSeeder::class);

        foreach ($tables as $table => $columns) {
            $rows = DB::table($table)->orderBy('id')->get();
            foreach ($columns as $column) {
                $this->assertSame($new.'/uploads/file.png', $rows[0]->$column);
                foreach ([1, 2] as $index) {
                    if ($column === 'fbx_file') {
                        $expected = $map;
                        $expected['fbx']['url'] = $new.'/uploads/model.fbx?download=1#file';
                        $expected['obj'] = $new.'/uploads/model.obj';
                        $this->assertSame($expected, json_decode($rows[$index]->$column, true));
                    } else {
                        $this->assertSame($new.'/uploads/file.png', $rows[$index]->$column);
                    }
                }
                $this->assertNull($rows[3]->$column);
                $this->assertSame('', $rows[4]->$column);
                $this->assertSame('https://cdn.example.test/file.png', $rows[5]->$column);
                $this->assertSame($old.'.example.test/file.png', $rows[6]->$column);
                $this->assertSame('uploads/file.png', $rows[7]->$column);
            }
            $this->assertSame([$old.'/must-stay'], $rows->pluck('description')->unique()->values()->all());
            $snapshots[$table] = $rows->toJson();
        }

        $this->seed(ReplaceLevelFileUrlsSeeder::class);
        foreach ($tables as $table => $columns) {
            $this->assertSame($snapshots[$table], DB::table($table)->orderBy('id')->get()->toJson());
        }
    }

    public function test_it_rejects_an_invalid_app_url_before_writing(): void
    {
        config(['app.url' => '/relative']);
        $this->expectException(InvalidArgumentException::class);
        $this->seed(ReplaceLevelFileUrlsSeeder::class);
    }
}
