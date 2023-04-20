<div>
    <x-modals.modal size="modal-xl" id="edit-video-modal-{{$videoDb->id}}" title="بارگذاری فیلم آموزشی">
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

        <x-forms.verification id="{{ $videoDb->id }}"/>

        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
