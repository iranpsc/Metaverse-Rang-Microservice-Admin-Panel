<div>
    <div id="edit-message-{{ $message->id }}" class="modal fade" wire:ignore.self role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">ویرایش پیام</h4>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <x-alert :type="'success'" :message="session('success')"></x-alert>
                    @endif
                    <div class="form-group">
                        <label class="form-col-label">متن پیام</label>
                        <textarea class="form-control form-control-sm rounded" wire:model.defer="message_content" rows="10">{{ $message->message }}</textarea>
                        @error('message_content')
                            <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group row">
                        <div class="col-2">
                            <button class="btn btn-primary btn-sm round" wire:click="edit">ذخیره</button>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-danger round" data-bs-dismiss="modal">بستن</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
