<div>
    <x-modals.modal size="modal-xl" id="edit-video-modal-{{$videoDb->id}}" title="بارگذاری فیلم آموزشی">
        <x-forms.group label="عنوان آموزش" for="title-{{ $videoDb->id }}">
            <x-forms.input id="title" wire:model="title-{{ $videoDb->id }}" />
            @error('title')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description-{{ $videoDb->id }}" label="توضیحات متنی">
            <textarea id="description-{{ $videoDb->id }}" cols="30" rows="10" class="form-control rounded" wire:model="description"></textarea>
            @error('description')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تصویر" for="image-{{ $videoDb->id }}">
            <x-forms.input type="file" id="image-{{ $videoDb->id }}" wire:model="image" />
            <x-progress-bar/>
            @error('image')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="فایل ویدئو" for="video-{{ $videoDb->id }}">
            <x-forms.input type="file" id="video-{{ $videoDb->id }}" wire:model="video" />
            <x-progress-bar/>
            @error('video')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
