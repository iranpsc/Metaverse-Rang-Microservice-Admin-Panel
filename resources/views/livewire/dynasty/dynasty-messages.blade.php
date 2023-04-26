<div>
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-message">ایجاد پیام
    </x-buttons.btn-primary>
    <x-modals.modal id="create-message" size="modal-xl" title="تعریف پیام سلسله">
        <ul class="alert alert-primary fill">
            <li>لیست شورت کدهای پیامهای سلسله</li>
            <li>نسبت خانوادگی: [relationship]</li>
            <li>کد شهروندی دریافت کننده: [reciever-code]</li>
            <li>کد شهروندی ارسال کننده: [sender-code]</li>
            <li>تاریخ: [created_at]</li>
            <li>نام فرستنده: [sender-name]</li>
            <li>نام دریافت کننده: [reciever-name]</li>
        </ul>
        <x-forms.group for="type" label="عنوان پیام">
            <x-forms.select id="type" wire:model="type">
                <option selected value="">انتخاب کنید</option>
                <option value="requester_confirmation_message">پیام تایید درخواست کننده</option>
                <option value="reciever_message">پیام دریافت کننده درخواست</option>
                <option value="reciever_accept_message">پیام تایید پذیرش پیوستن به سلسله</option>
                <option value="requester_accept_message">پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست و پاداش دریافتی
                </option>
            </x-forms.select>
            @error('type')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="content" label="متن پیام">
            <label class="form-col-label">متن پیام</label>
            <textarea class="form-control form-control-sm rounded summernote" wire:model="content" rows="10"></textarea>
            @error('content')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <div class="row">
                <div class="col-sm-6">
                    <x-buttons.btn-primary class="btn-block" wire:click="save">ذخیره</x-buttons.btn-primary>
                </div>
                <div class="col-sm-6">
                    <x-buttons.btn-danger class="btn-block" data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                </div>
            </div>
        </x-slot>
    </x-modals.modal>
    @if ($dynastyMessages->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نوع پیام</th>
                <th>متن پیام</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($dynastyMessages as $message)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \App\Helpers\getDynastyMessageTitle($message->type) }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#view-{{ $message->id }}">مشاهده
                        </x-buttons.btn-primary>
                        <x-modals.modal id="view-{{ $message->id }}" title="مشاهده پیام">
                            <p class="modal-text">{{ $message->message }}</p>
                            <x-slot name="footer">
                                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                            </x-slot>
                        </x-modals.modal>
                    </td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal"
                        data-bs-target="#edit-message-{{ $message->id }}">ویرایش</x-buttons.btn-primary>
                        <x-buttons.btn-danger class="confirm" title="deleteDynastyMessage" id="{{ $message->id }}">حذف</x-buttons.btn-danger>
                        <livewire:dynasty.edit-messages :message="$message" :wire:key="'edit-message-'.$message->id">
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>پیامی تعریف نشده است</x-alerts.danger>
    @endif
</div>
