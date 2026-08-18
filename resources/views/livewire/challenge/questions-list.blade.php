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
                <th>پاسخ ها</th>
                <th>عملیات</th>
            </x-slot:headers>
            @foreach ($questions as $question)
                <tr wire:key="{{ $question->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $question->title }}</td>
                    <td>{{ $question->code }}</td>
                    <td>{{ jdate($question->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <x-button color="primary" data-bs-toggle="modal" data-bs-target="#answers-model-{{ $question->id }}">نمایش پاسخ‌ها</x-button>
                        <x-modal id="answers-model-{{ $question->id }}" title="پاسخ‌ها">
                            @if ($question->answers->count() > 0)
                                <x-table>
                                    <x-slot name="headers">
                                        <th>عنوان</th>
                                        <th>پاسخ صحیح</th>
                                        <th>تاریخ ایجاد</th>
                                    </x-slot>
                                    @foreach ($question->answers as $answer)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $answer->title }}</td>
                                            <td>{{ $answer->is_correct ? 'بله' : 'خیر' }}</td>
                                            <td>{{ jdate($answer->created_at)->format('Y/m/d') }}</td>
                                        </tr>
                                    @endforeach
                                </x-table>
                            @else
                                <x-alert type="warning" :message="'پاسخی ثبت نشده است!'" />
                            @endif
                            <x-slot name="footer">
                                <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                            </x-slot>
                        </x-modal>
                    </td>
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
