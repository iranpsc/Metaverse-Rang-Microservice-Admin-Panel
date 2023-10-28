<div>
    <x-slot name="pageTitle">
        مدیریت پیام های سلسله
    </x-slot>

    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#create-message">ایجاد پیام</x-button>

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

        <div class="form-group">
            <label>متن پیام</label>
            <div wire:ignore>
                <textarea id="content"></textarea>
            </div>
            @error('content')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>
        <x-slot name="footer">
            <div class="row">
                <div class="col-sm-6">
                    <x-button id="save-btn">ذخیره</x-button>
                </div>
                <div class="col-sm-6">
                    <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                </div>
            </div>
        </x-slot>
    </x-modals.modal>
    @if ($dynastyMessages->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نوع پیام</th>
                <th>متن پیام</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($dynastyMessages as $message)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $message->getMessageTitle() }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#view-{{ $message->id }}">مشاهده
                        </x-button>
                        <x-modals.modal id="view-{{ $message->id }}" title="مشاهده پیام">
                            <p class="modal-text">{{ $message->message }}</p>
                            <x-slot name="footer">
                                <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                            </x-slot>
                        </x-modals.modal>
                    </td>
                    <td>
                        <x-button data-bs-toggle="modal"
                        data-bs-target="#edit-message-{{ $message->id }}">ویرایش</x-button>
                        <x-button color="danger" class="confirm" title="deleteDynastyMessage" id="{{ $message->id }}">حذف</x-button>
                        <livewire:dynasty.edit-messages :message="$message" :wire:key="'edit-message-'.$message->id">
                    </td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="danger" :message="'پیامی برای سلسله تعریف نشده است'" />
    @endif

    <script>
        window.addEventListener('livewire:load', function() {
            var description = CKEDITOR.replace('content');
            var saveBtn = document.getElementById('save-btn');

            CKEDITOR.editorConfig = function( config ) {
                config.language = 'fa';
                config.uiColor = '#F7B42C';
                config.height = 300;
                config.toolbarCanCollapse = true;
            };

            saveBtn.addEventListener('click', function() {
                @this.set('content', description.getData());
                @this.call('save');
            });
        })
    </script>
</div>
