<div>
    <x-buttons.btn-success data-bs-toggle="modal" data-bs-target="#import-question-modal">درون
        ریزی</x-buttons.btn-success>

    <x-modals.modal id="import-question-modal" title="درون ریزی">
        <x-forms.group for="file" label="فایل درون ریزی">
            <x-forms.input type="file" wire:model="file" id="file" />
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
        </x-tables.table>
        {{ $questions->links() }}
    @else
        <x-alerts.danger>داده ای یافت نشد.</x-alerts.danger>
    @endif
</div>
