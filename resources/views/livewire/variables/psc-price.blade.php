<div class="mt-3">

    @if (session()->has('success'))
        <button class="btn btn-success btn-round btn-noty-type m-b-10" data-type="success">{{ session('success') }}</button>
    @endif
    @if (session()->has('error'))
        <button class="btn btn-danger btn-round btn-noty-type m-b-10" data-type="danger">{{ session('error') }}</button>
    @endif

    <form id="demo-form" wire:submit.prevent="save">
        <div class="row">
            <div class="col-md-2">
                <span>قیمت PSC</span>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control rounded" wire:model.defer="price"
                    placeholder="هر یک واحد چند تومان" onkeypress="return isNumberKey(event)">
                @error('price')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-7">
                        <input type="text" class="form-control rounded" wire:model.defer="phoneVerification"
                            placeholder="تایید پیامکی" onkeypress="return isNumberKey(event)">
                        @error('phoneVerification')
                            <span class="form-text text-danger">{{ $message }}</span>
                        @enderror

                    </div>
                    <div class="col-5">
                        <a href="javascript:void(0)" class="btn btn-success btn-sm rounded" wire:click="sendSMS">ارسال
                            پیامک تایید</a>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control rounded" wire:model.defer="access_password"
                    placeholder="رمز دسترسی">
                @error('accessPassword')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-2">
                {{-- <button class="g-recaptcha btn btn-primary btn-block rounded"
                    data-sitekey="6LchpFAhAAAAAAC4XxT7zmsfzhwheUnV0Q0Mr2ue" data-callback='onClick'
                    data-action='submit'>ثبت</button> --}}
                <button type="submit" class="btn btn-primary btn-block rounded">ثبت</button>
            </div>
        </div>
    </form>

</div>
