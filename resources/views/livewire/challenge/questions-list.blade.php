<div>
    @if(session()->has('question-deleted'))
        <x-alerts.success>{{ session()->get('question-deleted') }}</x-alerts.success>
    @endif
    <x-buttons.btn-success data-bs-toggle="modal" data-bs-target="#create-question-modal">ایجاد سوال
    </x-buttons.btn-success>
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
        <x-slot name="footer">
            <x-buttons.btn-success wire:click="create">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>


    <x-tables.table class="mt-2">
        <x-slot:headers>
            <th>عنوان</th>
            <th>کد</th>
            <th>تاریخ ایجاد</th>
            <th>عملیات</th>
        </x-slot:headers>
        @forelse($questions as $question)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $question->title }}</td>
                <td>{{ $question->code }}</td>
                <td>{{ \Morilog\Jalali\Jalalian::forge($question->created_at)->format('Y/m/d') }}</td>
                <td>
                    <x-buttons.btn-success>نمایش</x-buttons.btn-success>
                    <x-buttons.btn-primary data-bs-target="#edit-question-modal-{{$question->id}}" data-bs-toggle="modal">ویرایش</x-buttons.btn-primary>
                    <x-buttons.btn-danger wire:click="delete({{ $question }})">حذف</x-buttons.btn-danger>
                </td>
            </tr>
            @livewire('challenge.edit-question', ['question' => $question], key('question-'.$question->id))
        @empty
            <x-alerts.danger>هیچ داده ای یافت نشد</x-alerts.danger>
        @endforelse
    </x-tables.table>
</div>
