<div>
    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#upload-video-modal">بارگذاری ویدیو
    </x-buttons.btn-success>

    <x-modals.modal size="modal-xl" id="upload-video-modal" title="بارگذاری فیلم آموزشی">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

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
            <span class="text-success" wire:loading wire:target="image">در حال بارگذاری ...</span>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="فایل ویدئو" for="video">
            <x-forms.input type="file" id="video" wire:model="video" />
            <span class="text-success" wire:loading wire:target="video">در حال بارگذاری ...</span>
            @error('video')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>


        <x-forms.group label="کد شهروندی بارگذار" for="creator_code">
            <x-forms.input id="creator_code" wire:model="creator_code" placeholder="HM-" />
            @error('creator_code')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                    ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="code" placeholder="تایید پیامکی" />
                @error('code')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <x-forms.group label="رمز دسترسی" for="accessPassword">
            <x-forms.input type="password" id="accessPassword" wire:model="accessPassword" placeholder="رمز دسترسی" />
            @error('accessPassword')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

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
                <th>ملاحضات</th>
            </x-slot:headers>
            @foreach ($videos as $video)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $video->title }}</td>
                    <td>{{ $video->categoriable->name }}</td>
                    <td>
                        <a target="_blank" href="{{ asset('uploads/' . $video->image) }}"
                            class="btn btn-sm btn-primary round">مشاهده</a>
                    </td>
                    <td>
                        <a target="_blank" href="{{ asset('uploads/' . $video->fileName) }}"
                            class="btn btn-sm btn-primary round">مشاهده</a>
                    </td>
                    <td>{{ $video->creator_code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($video->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($video->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-target="#edit-video-modal-{{$video->id}}" data-bs-toggle="modal">ویرایش</x-buttons.btn-primary>
                        <x-buttons.btn-danger title="deleteTrainingVideo" class="confirm" id="{{ $video->id }}">حذف
                        </x-buttons.btn-danger>
                    </td>
                </tr>
                <livewire:videos.edit-video :videoDb="$video" :wire:key="'edit-video'.$video->id">
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>ویدئویی بارگذاری نشده است.</x-alerts.danger>
    @endif
</div>
