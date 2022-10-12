
<div id="change-password-modal" class="modal fade" wire:ignore.self role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h4 class="modal-title">تغییر رمز عبور</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    @if(session('success'))
                        <x-alerts.success>{{session('success')}}</x-alerts.success>
                    @endif
                    <div class="offset-lg-0 col-md-12 offset-md-2 border shadow p-b-30">
                        <p class="text-center m-t-40 m-b-40">
                            <i class="icon-lock border border-primary img-circle text-primary font-xxxlg p-20"></i>
                        </p>
                        <hr>
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="old_password">رمز عبور پیشین</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-lock"></i>
                                        </span>
                                        <input type="password" id="old_password" wire:model="current_password" class="form-control ltr text-left">
                                        @error('current_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password">رمز عبور جدید</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-key"></i>
                                        </span>
                                        <input type="password" id="password" wire:model="password" minlength="5" class="form-control ltr text-left" required>
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">رمز عبور</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-key"></i>
                                        </span>
                                        <input type="password" id="password_confirmation" wire:model="password_confirmation" minlength="5" class="form-control ltr text-left" required>
                                        @error('password_confirmation')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="save" class="btn btn-info btn-round">
                    <i class="icon-check"></i>
                    ذخیره
                </button>
                <button type="button" data-bs-dismiss="modal" class="btn btn-warning btn-round pull-left">
                    <i class="icon-close"></i>
                    بازگشت
                </button>
            </div>
        </div>
    </div>
</div>
