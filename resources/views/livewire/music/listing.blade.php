<div>
    <x-buttons.btn-primary class="mb-2" data-bs-toggle="modal" data-bs-target="#create-music-modal">بارگذاری موسیقی</x-buttons.btn-primary>
    <x-modals.modal title="بارگذاری موسیقی" id="create-music-modal">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="category" label="دسته بندی">
            <select wire:model="category" class="form-control rounded">
                <option value="0">انتخاب کنید</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
                @error('category')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </select>
        </x-forms.group>
        <x-forms.group for="name" label="نام">
            <x-forms.input wire:model.lazy="name"/>
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="singer" label="نام خواننده">
            <x-forms.input wire:model.lazy="singer"/>
            @error('singer')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="song" label="فایل موسیقی">
            <x-forms.input type="file" wire:model="song"/>
            <span class="text-success" wire:loading wire:target="song">درحال بارگذاری</span>
            @error('song')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="cover" label="تصویر">
            <x-forms.input type="file" wire:model="cover"/>
            <span class="text-success" wire:loading wire:target="cover">در حال بارگذاری</span>
            @error('cover')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($musics->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام</th>
                <th>دسته بندی</th>
                <th>تصویر</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($musics as $music)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $music->name }}</td>
                    <td>{{ $music->category->name }}</td>
                    <td>
                        @isset($music->image)
                            <a href="{{ $music->image->url }}" class="btn btn-primary btn-sm rounded">مشاهده</a>
                        @endisset
                    </td>
                    <td>
                        <x-buttons.btn-danger class="confirm" title="deleteMusic" id="{{ $music->id }}">بستن</x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $musics->links() }}
    @else
        <x-alerts.danger>موسیقی بارگذاری نشده است.</x-alerts.danger>
    @endif
</div>
