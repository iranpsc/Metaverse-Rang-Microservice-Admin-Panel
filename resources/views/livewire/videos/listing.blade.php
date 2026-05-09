<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#upload-video-modal">بارگذاری ویدیو</x-button>

    <x-form.search-box wire:model.live="search" />

    <x-modal size="modal-xl" id="upload-video-modal" title="بارگذاری فیلم آموزشی">
        <x-form.input name="title" label="عنوان آموزش" placeholder="عنوان آموزش را وارد کنید" />

        <div class="form-group">
            <label for="description">توضیحات</label>
            <div wire:ignore>
                <textarea name="description" id="description"></textarea>
            </div>
            @error('description')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group row">
            <label class="form-col-label col-sm-4" for="category">دسته بندی</label>
            <div class="col-sm-8">
                <select class="form-control rounded" id="category" wire:model.live="category">
                    @if ($videoCategories)
                        <option value="">انتخاب کنید</option>
                        @foreach ($videoCategories as $videoCategory)
                            <option value="{{ $videoCategory->id }}">{{ $videoCategory->name }}</option>
                        @endforeach
                    @else
                        <option value="" selected>دسته بندی تعریف نشده است.</option>
                    @endif
                </select>
                @error('category')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row form-group">
            <label for="sub_category" class="col-sm-4 col-form-label">ویدیو مربوط به کدام زیر دسته است؟</label>
            <div class="col-sm-8">
                <select id="sub_category" class="form-control rounded" wire:model.live="sub_category">
                    @if ($videoSubCategories)
                        <option value="">انتخاب کنید</option>
                        @foreach ($videoSubCategories as $subCategory)
                            <option value="{{ $subCategory->id }}">{{ $subCategory->name }}</option>
                        @endforeach
                    @else
                        <option value="" selected>زیر دسته تعریف نشده است.</option>
                    @endif
                </select>
                @error('category')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input name="image" label="تصویر آموزش" type="file" />

        <div class="row form-group">
            <label for="videoFile" class="col-sm-4 col-form-label">فایل ویدئو</label>
            <div class="col-sm-8">
                <span id="videoFile" style="cursor: pointer" wire:ignore class="form-control rounded">Choose File</span>
                <x-progress-bar />
                <span class="form-text text-danger d-none" id="internet-disconnected-alert">اینترنت متصل نیست. به محض
                    اتصال
                    مجدد بارگذاری ادامه خواهد یافت.</span>
                @error('video')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input name="creator_code" label="کد شهروندی بارگذار" placeholder="hm-" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot:footer>
            <x-button id="save-btn">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
        </x-modals.modal>

        @if ($videos->count() > 0)
            <x-table>
                <x-slot:headers>
                    <th>عنوان</th>
                    <th>دسته</th>
                    <th>تصویر</th>
                    <th>فایل</th>
                    <th>ایجاد کننده</th>
                    <th>تاریخ ایجاد</th>
                    <th>تعداد بازدید</th>
                    <th>لایک ها</th>
                    <th>دیسلایک ها</th>
                    <th>ملاحضات</th>
                </x-slot:headers>
                @foreach ($videos as $video)
                    <tr wire:key="{{ $video->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $video->title }}</td>
                        <td>{{ $video->category->name }}</td>
                        <td>
                            <a target="_blank" href="{{ asset('uploads/' . $video->image) }}"
                                class="btn btn-sm btn-primary round">مشاهده</a>
                        </td>
                        <td>
                            <a target="_blank" href="{{ asset('uploads/' . $video->fileName) }}"
                                class="btn btn-sm btn-primary round">مشاهده</a>
                        </td>
                        <td>{{ $video->creator_code }}</td>
                        <td>{{ jdate($video->created_at)->format('Y/m/d') }}</td>
                        <td>{{ $video->views->count() }}</td>
                        <td>{{ $video->interactions->where('liked', 1)->count() }}</td>
                        <td>{{ $video->interactions->where('liked', 0)->count() }}</td>
                        <td>
                            <x-button data-bs-target="#edit-video-modal-{{ $video->id }}"
                                data-bs-toggle="modal">ویرایش</x-button>
                            <x-button color="danger" wire:confirm="آیا از حذف این ویدئو مطمئن هستید؟"
                                wire:click="delete({{ $video->id }})">حذف</x-button>
                            <livewire:videos.edit-video :videoDb="$video" :key="$video->id" />
                        </td>
                    </tr>
                @endforeach
            </x-table>
            {{ $videos->links() }}
        @else
            <x-alert type="danger" :message="'ویدئویی ثبت نشده است!'" />
        @endif

</div>

@script
    <script>
        let browseFile = document.getElementById('videoFile');
        let progress = browseFile.nextElementSibling;
        let progressBar = progress.querySelector('.progress-bar');
        let videoDisconnectedAlert = document.getElementById('internet-disconnected-alert');

        let resumable = new Resumable({
            target: '{{ route('videos.upload') }}',
            fileType: ['mp4'],
            chunkSize: 1 * 1024 * 1024, // 1MB
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            testChunks: false,
            throttleProgressCallbacks: 1,
            maxFiles: 1,
        });

        resumable.assignBrowse(browseFile);

        resumable.on('fileAdded', function(file) {
            resumable.upload();
            showProgress();
        });

        resumable.on('fileProgress', function(file) {
            updateProgress(Math.floor(file.progress() * 100));
        });

        resumable.on('fileSuccess', function(file, response) {
            response = JSON.parse(response)
            $wire.set('video', response.file_name);
            browseFile.innerText = response.file_name;
            hideProgress();
        });

        resumable.on('fileError', function(file, response) {
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
        });


        function showProgress() {
            progress.classList.remove('d-none');
            progress.classList.add('d-block');
            progressBar.style.width = '0%';
            progressBar.innerText = '0%';
        }

        function updateProgress(value) {
            progressBar.style.width = `${value}%`;
            progressBar.innerText = `${value}%`;
        }

        function hideProgress() {
            progress.classList.remove('d-block');
            progress.classList.add('d-none');
        }

        window.addEventListener('offline', function() {
            resumable.pause();
            videoDisconnectedAlert.classList.remove('d-none');
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
        });

        window.addEventListener('online', function() {
            resumable.upload();
            videoDisconnectedAlert.classList.add('d-none');
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-success');
        });

        let description = CKEDITOR.replace('description');
        let saveBtn = document.getElementById('save-btn');

        CKEDITOR.editorConfig = function(config) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('description', description.getData());
            $wire.call('save');
        });
    </script>
@endscript
