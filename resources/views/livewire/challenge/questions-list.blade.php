<div>
    {{-- <x-buttons.btn-success data-bs-toggle="modal" data-bs-target="#create-question-modal">ایجاد سوال
    </x-buttons.btn-success> --}}
    <x-buttons.btn-success data-bs-toggle="modal" data-bs-target="#import-question-modal">درون ریزی</x-buttons.btn-success>

    <x-modals.modal id="create-question-modal" title="ایجاد سوال">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="title" label="عنوان">
            <x-forms.input wire:model="title" id="title"/>
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="code" label="کد سوال">
            <x-forms.input wire:model="code" id="code"/>
            @error('code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="prize" label="مقدار پاداش">
            <x-forms.input wire:model="prize" id="prize"/>
            @error('prize')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="creator_code" label="کد شهروندی بارگذار">
            <x-forms.input wire:model="creator_code" id="creator_code"/>
            @error('creator_code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="image" label="تصویر">
            <x-forms.input type="image" wire:model="image" id="image"/>
            <span wire:loading wire:target="image" class="text-success">درحال بارگزاری...</span>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-slot name="footer">
            <x-buttons.btn-success wire:click="create">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <x-modals.modal id="import-question-modal" title="درون ریزی">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="file" label="فایل درون ریزی">
            <x-forms.input type="file" wire:model="file" id="file"/>
            <span wire:loading wire:target="file" class="text-success">درحال بارگزاری...</span>
            @error('file')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:click="upload">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($questions->count() > 0)
        <x-tables.table class="mt-2">
            <x-slot:headers>
                <th>عنوان</th>
                <th>کد</th>
                <th>تاریخ ایجاد</th>
                <th>عملیات</th>
            </x-slot:headers>
            @foreach($questions as $question)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $question->title }}</td>
                    <td>{{ $question->code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($question->created_at)->format('Y/m/d') }}</td>
                    <td>
                        {{-- <x-buttons.btn-success data-bs-target="#show-question-modal-{{$question->id}}"
                                            data-bs-toggle="modal">نمایش
                        </x-buttons.btn-success> --}}
                        @if( $question->answers->count() < 4 )
                        <x-buttons.btn-success data-bs-target="#create-question-answers-modal-{{$question->id}}"
                                            data-bs-toggle="modal">اضافه کردن پاسخ
                        </x-buttons.btn-success>
                        @endif
                        <x-buttons.btn-primary data-bs-target="#edit-question-modal-{{$question->id}}"
                                            data-bs-toggle="modal">ویرایش
                        </x-buttons.btn-primary>
                        <x-buttons.btn-danger wire:click="delete({{ $question }})">حذف</x-buttons.btn-danger>
                    </td>
                </tr>
                @livewire('challenge.edit-question', ['question' => $question], key('question-'.$question->code))
            @if($question->answers->count() < 4)
                @livewire('challenge.create-question-answers', ['question' => $question], key('question-'.$question->id))
            @endif
            <x-modals.modal id="show-question-modal-{{$question->id}}" title="نمایش اطلاعات سوال">
                <div class="row">
                    <div class="col-sm-12">
                        <x-forms.group for="title" label="عنوان">
                            <span>{{ $title }}</span>
                        </x-forms.group>

                        <div class="col-sm-12">
                            <x-forms.group for="code" label="کد">
                               <span>{{ $code }}</span>
                            </x-forms.group>
                        </div>

                        <div class="col-sm-12">
                            <x-forms.group for="code" label="لیست پاسخ های سوال">
                                @foreach( $question->answers as $answer)
                                    <span class="badge badge-info">{{ $answer->answer }}</span>
                                @endforeach
                            </x-forms.group>
                        </div>
                    </div>
                    <x-slot:footer>
                        <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                    </x-slot:footer>
            </x-modals.modal>
            @endforeach
        </x-tables.table>
        {{ $questions->links() }}
    @else
        <x-alerts.danger>داده ای یافت نشد.</x-alerts.danger>
    @endif


</div>
