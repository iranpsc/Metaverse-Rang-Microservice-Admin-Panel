<div>
    @if ($tickets->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد پیام</th>
                <th>تاریخ ارسال</th>
                <th>نام فرستنده</th>
                <th>ایمیل</th>
                <th>تلفن همراه</th>
                <th>عنوان</th>
                <th>درجه ارزش</th>
                <th>وضعیت</th>
                <th>پاسخ دهنده</th>
                <th>مدیریت</th>
            </x-slot:headers>
            @foreach ($tickets as $ticket)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ticket->code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($ticket->created_at)->format('Y/m/d') }}</td>
                    <td>{{ $ticket->sender->name }}</td>
                    <td>{{ $ticket->sender->email }}</td>
                    <td>{{ $ticket->sender->phone }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>{{ \App\Helpers\getTicketPriorityTitle($ticket->importance) }}</td>
                    <td>
                        @switch($ticket->status)
                            @case(0)
                                <i class="badge badge-primary">جدید</i>
                            @break

                            @case(1)
                                <i class="badge badge-success">پاسخ داده شده</i>
                            @break

                            @case(3)
                                <i class="badge badge-info">درحال بررسی</i>
                            @break

                            @case(4)
                                <i class="badge badge-success">بسته شده</i>
                            @break
                        @endswitch
                    </td>
                    <td>{{ $ticket->responser_name }}</td>
                    <td>
                        <x-buttons.btn-success data-bs-toggle="modal"
                            data-bs-target="#protection-modal-{{ $ticket->id }}">مشاهده</x-buttons.btn-success>
                        @if ($ticket->status != 1)
                            <x-buttons.btn-primary data-bs-toggle="modal"
                                data-bs-target="#protection-modal-send-to-{{ $ticket->id }}">ارجا به
                            </x-buttons.btn-primary>
                        @endif
                    </td>
                </tr>
                <x-modals.modal id="protection-modal-{{ $ticket->id }}" title="چزئیات تیکت">
                    @if (session('success'))
                        <x-alerts.success>{{ session('success') }}</x-alerts.success>
                    @endif
                    <span>شماره تیکت: {{ $ticket->code }}</span>
                    <h5 class="modal-title">عنوان: {{ $ticket->title }}</h5>
                    <p class="modal-text">متن: {{ $ticket->content }}</p>
                    <hr>

                    @if ($ticket->status != 1)
                        <label for="response-{{ $ticket->id }}">متن پاسخ:</label>
                        <textarea wire:model="response" id="response-{{ $ticket->id }}" class="form-control rounded" cols="30"
                            rows="3" placeholder="متن پاسخ را تایپ کنید..."></textarea>
                        @error('response')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        <x-forms.group class="my-3" for="attachment-{{ $ticket->id }}" label="پیوست">
                            <x-forms.input wire:model="attachment" type="file"
                                id="attachment-{{ $ticket->id }}" />
                        </x-forms.group>
                        @error('attachment')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    @endif


                    <x-slot:footer>
                        @if ($ticket->status != 1)
                            <x-buttons.btn-success class="btn-block" wire:loading.attr="disabled"
                                wire:click="sendResponse({{ $ticket->id }})">ارسال پاسخ</x-buttons.btn-success>
                        @endif
                        <x-buttons.btn-danger class="btn-block" data-bs-dismiss="modal">بستن</x-buttons.danger>
                    </x-slot:footer>

                </x-modals.modal>

                <x-modals.modal id="protection-modal-send-to-{{ $ticket->id }}" title="ارجا به بخش دیگر">
                    @if (session('success'))
                        <x-alerts.success>{{ session('success') }}</x-alerts.success>
                    @endif
                    <div class="alert alert-info">درصورتی که این تیکت به حوضه شما مربوط نمی باشد می توانید به بخش مربوطه
                        ارجا دهید</div>
                    <x-forms.group for="divert-to-{{ $ticket->id }}" label="ارجا به">
                        <select wire:model="department" id="divert-to-{{ $ticket->id }}"
                            class="form-control rounded">
                            <option>انتخاب کنید</option>
                            @if ($ticket->department != 'technical_support')
                                <option value="technical_support">پشتیبانی فنی</option>
                            @endif
                            @if ($ticket->department != 'citizens_safety')
                                <option value="citizens_safety">امنیت شهروندان</option>
                            @endif
                            @if ($ticket->department != 'investment')
                                <option value="investment">سرمایه گذاری</option>
                            @endif
                            @if ($ticket->department != 'inspection')
                                <option value="inspection">بازرسی</option>
                            @endif
                            @if ($ticket->department != 'protection')
                                <option value="protection">حراست</option>
                            @endif
                            @if ($ticket->department != 'ztb')
                                <option value="ztb">مدیریت کل ز.ت.ب</option>
                            @endif
                        </select>
                    </x-forms.group>

                    @error('department')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <x-forms.group for="importance-{{ $ticket->id }}" label="درجه ارزش">
                        <select wire:model="importance" id="importance-{{ $ticket->id }}"
                            class="form-control rounded">
                            <option selected>انتخاب کنید</option>
                            <option value="-1">کم</option>
                            <option value="0">متوسط</option>
                            <option value="1">زیاد</option>
                        </select>
                    </x-forms.group>
                    @error('importance')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <x-slot:footer>
                        <x-buttons.btn-primary wire:click="sendTo({{ $ticket->id }})">ارجا</x-buttons.btn-primary>
                        <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                    </x-slot:footer>

                </x-modals.modal>
            @endforeach
        </x-tables.table>
        {{ $tickets->links() }}
    @else
        <x-alerts.danger>تیکتی دریافت نشده است</x-alerts.danger>
    @endif
</div>
