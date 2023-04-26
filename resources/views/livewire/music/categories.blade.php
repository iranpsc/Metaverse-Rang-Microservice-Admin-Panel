<div>
    <x-buttons.btn-primary class="mb-2" data-bs-toggle="modal" data-bs-target="#create-music-category-modal">ایجاد دسته بندی</x-buttons.btn-primary>
    <x-modals.modal title="ایجاد دسته بندی موسیقی" id="create-music-category-modal">
        <x-forms.group for="name" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name"/>
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($categories->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($category->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($category->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" title="deleteMusicCategory" id="{{ $category->id }}">بستن</x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $categories->links() }}
    @else
        <x-alerts.danger>دسته بندی تعریف نشده است.</x-alerts.danger>
    @endif
</div>
