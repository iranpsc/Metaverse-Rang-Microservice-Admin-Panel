@props(['id', 'title', 'size' => 'modal-lg', 'footer' => ''])

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
            <div class="modal-footer">{{ $footer }}</div>
        </div>
    </div>
</div>
