<div>
    <x-slot name="pageTitle">
        لیست سوالات
    </x-slot>

    <x-forms.search-box wire:model="search" />

    <x-button data-bs-toggle="modal" data-bs-target="#import-question-modal">درون ریزی</x-button>

    <x-modals.modal id="import-question-modal" title="درون ریزی">
        <x-forms.group for="file" label="فایل درون ریزی">
            <x-forms.input type="file" wire:model="file" id="file" />
            <span wire:loading wire:target="file" class="text-success">درحال بارگزاری...</span>
            @error('file')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-button wire:click="upload">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($questions->count() > 0)
        <x-table class="mt-2">
            <x-slot:headers>
                <th>عنوان</th>
                <th>کد</th>
                <th>تاریخ ایجاد</th>
                <th>عملیات</th>
            </x-slot:headers>
            @foreach ($questions as $question)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $question->title }}</td>
                    <td>{{ $question->code }}</td>
                    <td>{{ jdate($question->created_at)->format('Y/m/d') }}</td>
                    <td>
                        --
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $questions->links() }}
    @else
        <x-alert type="danger" :message="'سوالی ثبت نشده است!'"/>
    @endif
</div>
