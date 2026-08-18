<div>
    <x-modal size="modal-xl" id="edit-video-modal-{{ $videoDb->id }}" title="بارگذاری فیلم آموزشی">
        <x-form.input name="title" label="عنوان آموزش" placeholder="عنوان آموزش را وارد کنید" />

        <div class="form-group">
            <label for="description">توضیحات</label>
            <div wire:ignore>
                <textarea name="description" id="description-{{ $videoDb->id }}">{{ $description }}</textarea>
            </div>
            @error('description')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group row">
            <label class="form-col-label col-sm-4" for="image">تصویر</label>
            <div class="col-sm-8">
                <input type="file" id="image-{{ $videoDb->id }}" class="form-control rounded" wire:model="image">
                <x-progress-bar />
                <span class="form-text text-danger d-none" id="internet-disconnected-alert">اینترنت متصل نیست. به محض
                    اتصال
                    مجدد بارگذاری ادامه خواهد یافت.</span>
                @error('image')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row form-group">
            <label for="videoFile" class="col-sm-4 col-form-label">فایل ویدئو</label>
            <div class="col-sm-8">
                <span id="videoFile-{{ $videoDb->id }}" style="cursor: pointer" wire:ignore class="form-control rounded">Choose File</span>
                <x-progress-bar />
                <span class="form-text text-danger d-none" id="internet-disconnected-alert">اینترنت متصل نیست. به محض
                    اتصال
                    مجدد بارگذاری ادامه خواهد یافت.</span>
                @error('video')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        @production
            <x-form.verification />
        @endproduction

        <x-slot:footer>
            <x-button id="save-btn-{{ $videoDb->id }}">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>

</div>

@script
    <script>
        let browseFile = document.getElementById('videoFile-{{ $videoDb->id }}');
        let progress = browseFile.nextElementSibling;
        let progressBar = progress.querySelector('.progress-bar');
        let internetDisconnectedAlert = document.getElementById('internet-disconnected-alert');

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
            internetDisconnectedAlert.classList.remove('d-none');
            resumable.pause();
        });

        window.addEventListener('online', function() {
            internetDisconnectedAlert.classList.add('d-none');
            resumable.upload();
        });

        let description = CKEDITOR.replace(document.getElementById('description-{{ $videoDb->id }}'));
        let saveBtn = document.getElementById('save-btn-{{ $videoDb->id }}');

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
