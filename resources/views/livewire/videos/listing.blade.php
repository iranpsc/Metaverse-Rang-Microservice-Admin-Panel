<div>
    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#upload-video-modal">بارگذاری ویدیو
    </x-buttons.btn-success>

    <x-modals.modal size="modal-xl" id="upload-video-modal" title="بارگذاری فیلم آموزشی">
        <x-forms.group label="عنوان آموزش" for="title">
            <x-forms.input id="title" wire:model="title" />
            @error('title')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description" label="توضیحات متنی">
            <textarea id="description" cols="30" rows="10" class="form-control rounded" wire:model="description"></textarea>
            @error('description')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="category" label="ویدیو مربوط به کدام دسته است؟">
            <x-forms.select id="category" wire:model="category">
                @if ($videoCategories->count() > 0)
                    <option value="">انتخاب کنید</option>
                    @foreach ($videoCategories as $videoCategory)
                        <option value="{{ $videoCategory->id }}">{{ $videoCategory->name }}</option>
                    @endforeach
                @else
                    <option value="" selected>دسته بندی تعریف نشده است.</option>
                @endif
            </x-forms.select>
            @error('category')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="subCategory" label="ویدیو مربوط به کدام زیر دسته است؟">
            <x-forms.select id="subCategory" wire:model.lazy="subCategory">
                @if (!is_null($videoSubCategories))
                    <option value="">انتخاب کنید</option>
                    @foreach ($videoSubCategories as $subCategory)
                        <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                    @endforeach
                @else
                    <option value="" selected>زیر دسته تعریف نشده است.</option>
                @endif
            </x-forms.select>
            @error('category')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image">
            <x-forms.input type="file" id="image" wire:model="image" />
            <x-progress-bar />
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="فایل ویدئو" for="video">
            <x-forms.input type="file" id="video" wire:model="video" />
            <x-progress-bar />
            @error('video')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-forms.group label="کد شهروندی بارگذار" for="creator_code">
            <x-forms.input id="creator_code" wire:model="creator_code" placeholder="hm-" />
            @error('creator_code')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification />

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>

    @if ($videos->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>عنوان</th>
                <th>دسته</th>
                <th>تصویر</th>
                <th>فایل</th>
                <th>ایجاد کننده</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>تعداد بازدید</th>
                <th>لایک ها</th>
                <th>دیسلایک ها</th>
                <th>ملاحضات</th>
            </x-slot:headers>
            @foreach ($videos as $video)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $video->title }}</td>
                    <td>{{ $video->subCategory->name }}</td>
                    <td>
                        <a target="_blank" href="{{ $video->image }}" class="btn btn-sm btn-primary round">مشاهده</a>
                    </td>
                    <td>
                        <a target="_blank" href="{{ $video->fileName }}" class="btn btn-sm btn-primary round">مشاهده</a>
                    </td>
                    <td>{{ $video->creator_code }}</td>
                    <td>{{ jdate($video->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($video->created_at)->format('H:m:s') }}</td>
                    <td>{{ $video->views->count() }}</td>
                    <td>{{ $video->interactions->where('liked', 1)->count() }}</td>
                    <td>{{ $video->interactions->where('liked', 0)->count() }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-target="#edit-video-modal-{{ $video->id }}"
                            data-bs-toggle="modal">ویرایش</x-buttons.btn-primary>
                        <x-buttons.btn-danger title="deleteTrainingVideo" class="confirm" id="{{ $video->id }}">حذف
                        </x-buttons.btn-danger>
                        <livewire:videos.edit-video :videoDb="$video" :wire:key="'edit-video'.$video->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $videos->links() }}
    @else
        <x-alerts.danger>ویدئویی بارگذاری نشده است.</x-alerts.danger>
    @endif
</div>
