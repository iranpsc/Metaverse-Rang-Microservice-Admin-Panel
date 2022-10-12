<div>
    <div id="change-password-modal" class="modal fade" role="dialog" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">تیتر مودال</h4>
                </div>
                <div class="modal-body">
                    <div>

                        @if (session()->has('success'))
                            <x-alert :type="'success'" :message="session('success')"></x-alert>
                        @endif
                        @if (session()->has('error'))
                            <x-alert :type="'danger'" :message="session('error')"></x-alert>
                        @endif
                    </div>
                    <div class="form-body">
                        <div class="form-group">
                            <label for="current_password">رمز عبور فعلی</label>
                            <div class="input-group round">
                                <span class="input-group-addon">
                                    <i class="icon-key"></i>
                                </span>
                                <input type="password" id="password" wire:model.defer="current_password"
                                    name="current_password" minlength="5" class="form-control ltr text-left">
                                <hr>
                            </div>
                            @error('current_password')
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label for="password">رمز عبور جدید</label>
                        <div class="input-group round">
                            <span class="input-group-addon">
                                <i class="icon-key"></i>
                            </span>
                            <input type="password" id="password" name="password" wire:model.defer="password"
                                minlength="5" class="form-control ltr text-left">
                            <hr>
                        </div>
                        @error('password')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">رمز عبور</label>
                        <div class="input-group round">
                            <span class="input-group-addon">
                                <i class="icon-key"></i>
                            </span>
                            <input type="password" id="password_confirmation" wire:model.defer="password_confirmation"
                                name="password_confirmation" minlength="5" class="form-control ltr text-left">
                            <hr>

                        </div>
                        @error('password_confirmation')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" wire:click="save" class="btn btn-info btn-block btn-round">
                        <i class="icon-check"></i>
                        بروزرسانی
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-block btn-danger btn-round"
                    data-bs-dismiss="modal">بازگشت</button>
            </div>
        </div>
    </div>
</div>
