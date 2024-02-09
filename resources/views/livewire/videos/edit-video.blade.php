<div>
    <x-modals.modal size="modal-xl" id="edit-video-modal-{{ $videoDb->id }}" title="بارگذاری فیلم آموزشی">
        <x-forms.group label="عنوان آموزش" for="title-{{ $videoDb->id }}">
            <x-forms.input id="title-{{ $videoDb->id }}" wire:model="title" />
            @error('title')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="description-{{ $videoDb->id }}" label="توضیحات متنی">
            <div wire:ignore>
                <textarea id="description-{{ $videoDb->id }}">{{ $videoDb->description }}</textarea>
            </div>
            @error('description')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image-{{ $videoDb->id }}">
            <x-forms.input type="file" id="image-{{ $videoDb->id }}" wire:model="image" />
            <x-progress-bar />
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="فایل ویدئو" for="videoFile-{{ $videoDb->id }}">
            <span id="videoFile-{{ $videoDb->id }}" style="cursor: pointer" wire:ignore class="form-control rounded">Choose File</span>
            <x-progress-bar />
            <span class="form-text text-danger d-none" id="internet-disconnected-alert">اینترنت متصل نیست. به محض اتصال مجدد بارگذاری ادامه خواهد یافت.</span>
            @error('video')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification />

        <x-slot:footer>
            <x-buttons.btn-primary id="save-{{ $videoDb->id }}">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>

    <script>
        window.addEventListener('livewire:load', function() {
            let browseFile = document.getElementById('videoFile-{{ $videoDb->id }}');
            let progress = browseFile.nextElementSibling;
            let progressBar = progress.querySelector('.progress-bar');
            let internetDisconnectedAlert = document.getElementById('internet-disconnected-alert');

            let resumable = new Resumable({
                target: '{{ route('videos.edit.upload') }}',
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
                internetDisconnectedAlert.classList.remove('d-none');
                resumable.pause();
            });

            window.addEventListener('online', function() {
                internetDisconnectedAlert.classList.add('d-none');
                resumable.upload();
            });

            var description = CKEDITOR.replace(document.getElementById('description-{{ $videoDb->id }}'));
            var saveBtn = document.getElementById('save-{{ $videoDb->id }}');

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            saveBtn.addEventListener('click', function() {
                @this.set('description', description.getData());
                @this.call('save');
            });
        });
    </script>
</div>
