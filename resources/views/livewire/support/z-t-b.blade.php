<div>
    <x-slot name="pageTitle">
        مدیریت کل ز.ت.ب
    </x-slot>

    <x-forms.search-box wire:model="search" />

    @if ($tickets->count() > 0)
        <x-table>
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
                    <td>{{ jdate($ticket->created_at)->format('Y/m/d') }}</td>
                    <td>{{ $ticket->sender->name }}</td>
                    <td>{{ $ticket->sender->email }}</td>
                    <td>{{ $ticket->sender->phone }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td>{{ $ticket->getPriorityTitle() }}</td>
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
                        <x-button data-bs-toggle="modal" data-bs-target="#z-t-b-modal-{{ $ticket->id }}">
                            مشاهده</x-button>
                        <x-button data-bs-toggle="modal"
                            data-bs-target="#z-t-b-modal-send-to-{{ $ticket->id }}">ارجا به
                        </x-button>
                    </td>
                </tr>
                <x-modals.modal id="z-t-b-modal-{{ $ticket->id }}" title="چزئیات تیکت">
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
                            <x-button class="btn-block" wire:loading.attr="disabled"
                                wire:click="sendResponse({{ $ticket->id }})">ارسال پاسخ</x-button>
                        @endif
                        <x-button color="danger" class="btn-block" data-bs-dismiss="modal">بستن</x-button>
                    </x-slot:footer>

                </x-modals.modal>

                <x-modals.modal id="z-t-b-modal-send-to-{{ $ticket->id }}" title="ارجا به بخش دیگر">
                    <x-alert type="warning" :message="__('در صورتی که این تیکت به حوضه شما مربوط نمی باشد می توانید به بخش مربوطه ارجاع دهید.')"
                    class="mb-3" />
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
                        @if ($ticket->status != 1)
                            <x-button wire:click="sendTo({{ $ticket->id }})">ارجا
                            </x-button>
                        @endif
                        <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                    </x-slot:footer>

                </x-modals.modal>
            @endforeach
        </x-table>
        {{ $tickets->links() }}
    @else
        <x-alert type="warning" message="تیکتی یافت نشد!" />
    @endif
</div>
