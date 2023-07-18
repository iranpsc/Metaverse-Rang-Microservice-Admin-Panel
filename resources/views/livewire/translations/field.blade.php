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

    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-field">ایجاد عبارت جدید</x-buttons.btn-primary>

    <x-modals.modal id="create-field" title="ایجاد عبارت جدید">
        <x-forms.group for="name" label="نام عبارت">
            <x-forms.input wire:model.defer="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="translation" label="ترجمه">
            <x-forms.input wire:model.defer="translation" id="translation" />
            @error('translation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($fields->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>عبارت</th>
                <th>ترجمه</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($fields as $field)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $field->name }}</td>
                    <td>{{ $field->translation }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#edit-field-{{ $field->id }}">
                            <span class="fa fa-edit"></span>
                        </x-buttons.btn-primary>
                        <x-buttons.btn-danger id="deleteField-{{ $field->id }}">
                            <span class="close">&times;</span>
                        </x-buttons.btn-danger>
                        <livewire:translations.edit-field :field="$field" :key="'fields-'.$field->id" />
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>هیچ اطلاعاتی موجود نیست.</x-alerts.danger>
    @endif

    <script>
        window.addEventListener('livewire:load', function() {
            let deleteTranslation = document.querySelectorAll("[id^='deleteField-']");

            deleteTranslation.forEach(function(element) {
                element.addEventListener('click', function() {
                    let fieldId = element.id.split('-')[1];
                    Swal.fire({
                        title: 'آیا از حذف این عبارت مطمئن هستید؟',
                        text: "این عمل غیر قابل بازگشت است!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'بله، حذف کن!',
                        cancelButtonText: 'لغو'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deleteField', fieldId);
                        }
                    });
                });
            });
        });
    </script>
</div>
