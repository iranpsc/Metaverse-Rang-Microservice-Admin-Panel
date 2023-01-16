<?php

namespace App\Http\Livewire\Music;

use App\Models\Music;
use App\Models\MusicCategory;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = [
        'musicCreated' => '$refresh',
        'musicDeleted' => '$refresh',
        'deleteMusic'  => 'delete'
    ];
    public $name, $singer, $song, $cover, $category;

    protected $rules = [
        'name' => 'required|string',
        'singer' => 'required|string',
        'song' => 'required|mimes:mp3',
        'cover' => 'nullable|image',
        'category' => 'required|min:0'
    ];

    protected $messages = [
        'name.required' => 'نام موسیقی را وارد کنید.',
        'singer.required' => 'نام خواننده را وارد کنید.',
        'song.mimes' => 'فرمت موسیقی صحیح نیست. ',
        'song.required' => 'فایل موسیقی را بارگذاری کنید.',
        'cover.image' => 'فرمت تصویر صحیح نمی باشد.',
        'category.required' => 'دسته بندی را مشخص کنید.',
        'category.min' => 'دسته بندی را مشخص کنید.',
    ];

    public function save()
    {
        $this->validate();
        $song_url = $this->song->store('https://dl.qzparadise.ir/public/musics');
        $music = Music::create([
            'music_category_id' => MusicCategory::findOrFail($this->category)->id,
            'name' => $this->name,
            'singer' => $this->singer,
            'fileName' => $song_url,
        ]);
        if($this->cover) {
            $url = $this->cover->store('https://dl.qzparadise.ir/public/musics');
            $music->image()->create([
                'url' => $url
            ]);
        }
        $this->reset('name', 'singer', 'song', 'cover');
        session()->flash('success', 'موسیقی ایجاد شد.');
        $this->emitSelf('musicCreated');
    }

    public function render()
    {
        return view('livewire.music.listing', [
            'categories' => MusicCategory::all(),
            'musics' => Music::paginate(10),
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
