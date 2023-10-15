<div>

    <x-breadcrumb>
        <x-breadcrumb.item title="ترجمه ها" href="{{ route('translations') }}"/>
        <x-breadcrumb.item title="بخش ها" href="{{ route('modals', $modal->translation->id) }}"/>
        <x-breadcrumb.item title="تب ها" active="true" />
    </x-breadcrumb>
    <br>

    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-tab">ایجاد تب جدید</x-buttons.btn-primary>

    <x-modals.modal id="create-tab" title="ایجاد تب جدید">
        <x-forms.group for="name" label="نام تب">
            <x-forms.input wire:model="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($tabs->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام بخش</th>
                <th>پیشرفت</th>
                <th>تعداد</th>
                <th>انجام شده</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($tabs as $tab)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tab->name }}</td>
                    <td>
                        <div class="progress">
                            @php
                                $tab->progress = $tab->fields_count > 0 ? round(($tab->translated_fields_count / $tab->fields_count) * 100) : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $tab->progress }}%" aria-valuenow="{{ $tab->progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $tab->progress }}%
                            </div>
                        </div>
                    </td>
                    <td>{{ $tab->fields_count }}</td>
                    <td>{{ $tab->translated_fields_count }}</td>
                    <td>
                        <a href="{{ route('fields', [
                            'translation' => $tab->modal->translation->id,
                            'modal' => $tab->modal->id,
                            'tab' => $tab->id
                            ]) }}"
                        class="btn btn-primary rounded"><span class="fa fa-edit"></span></a>
                        <x-buttons.btn-danger id="deleteTab-{{ $tab->id }}">
                            <span class="fa fa-trash"></span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $tabs->links() }}
    @else
        <x-alerts.danger>هیچ اطلاعاتی موجود نیست.</x-alerts.danger>
    @endif

    <script>
        window.addEventListener('livewire:load', function() {
            let deleteTranslation = document.querySelectorAll("[id^='deleteTab-']");

            deleteTranslation.forEach(function(element) {
                element.addEventListener('click', function() {
                    let tabId = element.id.split('-')[1];
                    Swal.fire({
                        title: 'آیا از حذف این بخش مطمئن هستید؟',
                        text: "این عمل غیر قابل بازگشت است!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'بله، حذف کن!',
                        cancelButtonText: 'لغو'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deleteTab', tabId);
                        }
                    });
                });
            });
        });
    </script>
</div>
