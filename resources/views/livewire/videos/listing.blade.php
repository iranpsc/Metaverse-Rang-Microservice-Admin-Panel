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

        <x-forms.group label="فایل ویدئو" for="videoFile">
            <span id="videoFile" style="cursor: pointer" wire:ignore class="form-control rounded">Choose File</span>
            <x-progress-bar />
            <span class="form-text text-danger d-none" id="internet-disconnected-alert">اینترنت متصل نیست. به محض اتصال مجدد بارگذاری ادامه خواهد یافت.</span>
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
                    <td>{{ $video->id }}</td>
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

    <script>
        window.addEventListener('livewire:load', function() {
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
                @this.set('video', response.fileName);
                browseFile.innerText = response.fileName;
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
                videoDisconnectedAlert.classList.remove('d-none');
                resumable.pause();
            });

            window.addEventListener('online', function() {
                videoDisconnectedAlert.classList.add('d-none');
                resumable.upload();
            });
        });
    </script>
</div>
