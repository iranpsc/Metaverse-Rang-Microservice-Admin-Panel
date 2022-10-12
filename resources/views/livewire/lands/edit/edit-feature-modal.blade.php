<div>
    <div id="modal-{{explode("+",$properties_id)[1]}}" wire:ignore.self class="modal fade" data-bs-backdrop="static" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">ویرایش ملک</h4>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <i class="icon-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger fill">
                            <i class="icon-error"></i>
                            {{ session('error') }}
                        </div>
                    @endif


                    <div class="form-group">
                        <div class="row">
                            <label for="area" class="form-col-label col-sm-4">مساحت</label>
                            <div class="col-md-8">
                                <input type="text" wire:model="area" class="form-control rounded"
                                    value="{{ $area }}">
                                    @error('area')
                                        <span class="text-danger form-text">{{ $message }}</span>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="density" class="form-col-label col-sm-4">تراکم</label>
                            <div class="col-md-8">
                                <input type="text" wire:model="density" class="form-control rounded"
                                    value="{{ $density }}">
                                    @error('density')
                                        <span class="text-danger form-text">{{ $message }}</span>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="karbari" class="form-col-label col-sm-4">نوع کاربری</label>
                            <div class="col-md-8">
                                <input type="text" wire:model="karbari" class="form-control rounded"
                                    value="{{ $karbari }}">
                                    @error('karbari')
                                        <span class="text-danger form-text">{{ $message }}</span>
                                    @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="address" class="form-col-label col-sm-4">آدرس</label>
                            <div class="col-md-8">
                                <input type="text" wire:model="address" name="address" class="form-control rounded"
                                    value="{{ $address }}">
                                    @error('address')
                                        <span class="text-danger form-text">{{ $message }}</span>
                                    @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-4">
                            <a href="javascript:void(0)" class="btn btn-success btn-block btn-sm rounded" wire:click="sendSMS">
                                ارسال پیامک تایید
                            </a>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control rounded" wire:model.defer="phoneVerification"
                                placeholder="تایید پیامکی" onkeypress="return isNumberKey(event)">
                            @error('phoneVerification')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="row form-group">
                        <label for="access_password" class="form-col-label col-sm-4">رمز دسترسی</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control rounded" wire:model.defer="access_password"
                                placeholder="رمز دسترسی">
                            @error('access_password')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <button class="btn btn-danger rounded btn-sm btn-block"
                                data-bs-dismiss="modal">بستن</button>
                        </div>
                        <div class="col-sm-8">
                            {{-- <button class="g-recaptcha btn btn-primary btn-block rounded"
                            data-sitekey="6LchpFAhAAAAAAC4XxT7zmsfzhwheUnV0Q0Mr2ue" data-callback='onClick'
                            data-action='submit'>ثبت</button> --}}
                            <button type="submit" wire:click="save"
                                class="btn btn-primary btn-block rounded">ثبت</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
