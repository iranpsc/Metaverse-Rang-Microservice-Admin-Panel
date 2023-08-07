@props(['id', 'title' => 'عنوان مودال', 'size' => 'modal-lg'])

<div id="{{ $id }}" wire:ignore.self class="modal fade" data-bs-backdrop="static" role="dialog" tabindex="-1">
    <div class="modal-dialog {{ $size }} modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ $title }}</h4>
            </div>

            <div class="modal-body text-right">
                {{ $slot }}
            </div>

            <div class="modal-footer">
                <x-button color="success" id="store-btn">
                    <span class="fa fa-check"></span>
                </x-button>
                <x-button color="danger" data-bs-dismiss="modal">
                    <span class="fa fa-close"></span>
                </x-button>
            </div>
        </div>
    </div>
</div>
