<div>

    <x-button data-bs-toggle="modal" data-bs-target="#import">درون ریزی</x-button>

    <x-modal id="import" title="درون ریزی">
        <x-form.input label="فایل" name="import_file" type="file" accept=".xlsx, .csv" />
        <x-slot name="footer">
            <x-button wire:click="import">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>

    @if ($questions->count() > 0)
        <x-table class="mt-2">
            <x-slot:headers>
                <th>عنوان</th>
                <th>کد</th>
                <th>تاریخ ایجاد</th>
                <th>عملیات</th>
            </x-slot:headers>
            @foreach ($questions as $question)
                <tr wire:key="{{ $question->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $question->title }}</td>
                    <td>{{ $question->code }}</td>
                    <td>{{ jdate($question->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <x-button color="danger" wire:click="delete({{ $question->id }})"
                            wire:confirm="آیا از حذف این سوال اطمینان دارید؟">حذف</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $questions->links() }}
    @else
        <x-alert type="warning" :message="'سوالی ثبت نشده است!'" />
    @endif
</div>
