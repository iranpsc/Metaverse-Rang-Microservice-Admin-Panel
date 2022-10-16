<div>
    <div id="create-message" class="modal fade" wire:ignore.self role="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">تعریف پیام سلسله</h4>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <x-alerts.success>{{ session('success') }}</x-alerts.success>
                    @endif
                    <ul class="alert alert-info">
                        <li>لیست شورت کدهای پیامهای سلسله</li>
                        <li>نسبت خانوادگی: [relation]</li>
                        <li>کد شهروندی دریافت کننده: [reciever-code]</li>
                        <li>کد شهروندی ارسال کننده: [sender-code]</li>
                        <li>تاریخ: [date]</li>
                        <li>نام فرستنده: [sender]</li>
                        <li>نام دریافت کننده: [reciever]</li>
                    </ul>
                    <div class="form-group">
                        <label class="form-col-label">عنوان پیام</label>
                        <select class="form-control form-control-sm" wire:model="message.type">
                            <option selected>انتخاب کنید</option>
                            <option value="requester">پیام تایید درخواست کننده</option>
                            <option value="reciever">پیام دریافت کننده درخواست</option>
                            <option value="requester_accept">پیام تایید پذیرش پیوستن به سلسله</option>
                            <option value="reciever_accept">پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست
                            </option>
                            <option value="dynasty_prize">پیام پاداش برای درخواست کننده سلسله</option>
                        </select>
                        @error('message.type')
                            <span class="form-text text-danger">{{ $message }}</span>
                        @enderror

                    </div>
                    <div class="form-group">
                        <label class="form-col-label">متن پیام</label>
                        <textarea class="form-control form-control-sm rounded summernote" wire:model="message.message" rows="10"></textarea>
                        @error('message.message')
                            <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <button class="btn btn-primary btn-block rounded" wire:click="save">ذخیره</button>
                        </div>
                        <div class="col-sm-6">
                            <button class="btn btn-danger btn-block rounded" data-bs-dismiss="modal">بستن</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
