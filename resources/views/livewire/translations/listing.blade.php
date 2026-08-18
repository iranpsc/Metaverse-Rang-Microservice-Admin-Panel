<div>
    <div class="row">
        <div class="col-sm-8"></div>
        <div class="col-sm-4">
            <div class="input-group mb-3" wire:ignore>
                <select class="form-control round" id="languages" wire:model="selectedLanguage">
                    <option value="">انتخاب زبان</option>
                    @foreach ($languages as $key => $language)
                        <option value="{{ $key }}">{{ $language['name'] }}({{ $language['nativeName'] }})
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" wire:click="saveTranslation"
                        style="border-radius: 5px 0 0 5px">اضافه کردن ترجمه</button>
                </div>
            </div>
            @error('selectedLanguage')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @if ($translations->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>آیکون</th>
                <th>زبان</th>
                <th>پیشرفت</th>
                <th>تعداد</th>
                <th>انجام شده</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($translations as $translation)
                <tr wire:key="{{ $translation->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img src="{{ asset('assets/images/flags/' . Str::upper($translation->code)) }}.svg"
                            style="width: 30px; height: 30px">
                    </td>
                    <td>{{ $translation->name }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="checkbox" @checked($translation->status) />
                        <a href="{{ route('modals', $translation->id) }}" class="btn btn-primary rounded">
                            <span class="fa fa-edit"></span>
                        </a>

                        <x-button color="success" wire:click="export({{ $translation->id }})">
                            <span class="fa fa-upload"></span>
                        </x-button>

                        <x-button color="danger" wire:click="delete({{ $translation->id }})"
                            wire:confirm="آیا می خواهید حذف کنید؟">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">هیچ ترجمه ای یافت نشد!</td>
                </tr>
            @endforelse
        </x-table>
        {{ $translations->links() }}
    @else
        <x-alert type="warning" message="هیچ ترجمه ای یافت نشد!" />
    @endif
</div>
