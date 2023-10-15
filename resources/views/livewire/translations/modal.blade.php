<div>

    <x-breadcrumb>
        <x-breadcrumb.item title="ترجمه ها" href="{{ route('translations') }}"/>
        <x-breadcrumb.item title="بخش ها" active="true" />
    </x-breadcrumb>
    <br>

    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-modal">ایجاد بخش جدید</x-buttons.btn-primary>

    <x-modals.modal id="create-modal" title="ایجاد بخش جدید">
        <x-forms.group for="name" label="نام بخش">
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

    @if ($modals->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام بخش</th>
                <th>پیشرفت</th>
                <th>تعداد</th>
                <th>انجام شده</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($modals as $modal)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $modal->name }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <a href="{{ route('tabs', [
                            'translation' => $translation->id,
                            'modal' => $modal->id
                            ]) }}"
                        class="btn btn-primary rounded"><span class="fa fa-edit"></span></a>
                        <x-buttons.btn-danger id="deleteModal-{{ $modal->id }}">
                            <span class="fa fa-trash"></span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $modals->links() }}
    @else
        <x-alerts.danger>هیچ اطلاعاتی موجود نیست.</x-alerts.danger>
    @endif

    <script>
        window.addEventListener('livewire:load', function() {
            let deleteTranslation = document.querySelectorAll("[id^='deleteModal-']");

            deleteTranslation.forEach(function(element) {
                element.addEventListener('click', function() {
                    let modalId = element.id.split('-')[1];
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
                            @this.call('deleteModal', modalId);
                        }
                    });
                });
            });
        });
    </script>
</div>
