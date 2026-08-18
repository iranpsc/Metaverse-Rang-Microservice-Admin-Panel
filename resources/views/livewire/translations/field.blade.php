<div>
    <x-breadcrumb>
        <x-breadcrumb.item title="ترجمه ها" href="{{ route('translations') }}"/>
        <x-breadcrumb.item title="بخش ها" href="{{ route('modals', $tab->modal->translation->id) }}"/>
        <x-breadcrumb.item title="تب ها" href="{{ route('tabs', [
            'translation' => $tab->modal->translation->id,
            'modal' => $tab->modal->id,
            'tab' => $tab->id
        ]) }}" />
        <x-breadcrumb.item title="عبارات" active="true"/>
    </x-breadcrumb>
    <br>

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-field">ایجاد عبارت جدید</x-button>

    <x-modal id="create-field" title="ایجاد عبارت جدید">
        <x-form.input name="value" label="ترجمه" />

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($fields->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>شناسه یکتا</th>
                <th>ترجمه</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($fields as $field)
                <tr wire:key="{{ $field->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $field->unique_id }}</td>
                    <td>{{ $field->translation }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#edit-field-{{ $field->id }}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" wire:click="delete({{ $field->id }})" wire:confirm="آیا می خواهید این فیلد را حذف کنید؟">
                            <span class="fa fa-trash"></span>
                        </x-button>
                        <livewire:translations.edit-field :$field :key="$field->id" />
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $fields->links() }}
    @else
        <x-alert type="danger" message="هیچ اطلاعاتی موجود نیست"/>
    @endif
</div>
