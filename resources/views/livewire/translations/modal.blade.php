<div>

    <x-breadcrumb>
        <x-breadcrumb.item title="ترجمه ها" href="{{ route('translations') }}" />
        <x-breadcrumb.item title="بخش ها" active="true" />
    </x-breadcrumb>
    <br>

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-modal">ایجاد بخش جدید</x-button>

    <x-modal id="create-modal" title="ایجاد بخش جدید">
        <x-form.input name="name" label="نام بخش" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
        </x-modals.modal>

        @if ($modals->count() > 0)
            <x-table>
                <x-slot:headers>
                    <th>نام بخش</th>
                    <th>پیشرفت</th>
                    <th>تعداد</th>
                    <th>انجام شده</th>
                    <th>اقدام</th>
                </x-slot:headers>
                @forelse ($modals as $modal)
                    <tr wire:key="{{ $modal->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $modal->name }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <a href="{{ route('tabs', [
                                'translation' => $translation->id,
                                'modal' => $modal->id,
                            ]) }}"
                                class="btn btn-primary rounded"><span class="fa fa-edit"></span></a>
                            <x-button color="info" data-bs-toggle="modal"
                                data-bs-target="#edit-modal-{{ $modal->id }}">
                                <span class="fa fa-edit"></span>
                            </x-button>
                            <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟"
                                wire:click="delete({{ $modal->id }})">
                                <span class="fa fa-trash"></span>
                            </x-button>
                        </td>
                    </tr>
                    <livewire:translations.edit-modal :$modal :key="$modal->id" />
                @endforeach
            </x-table>
            {{ $modals->links() }}
        @else
            <x-alert type="warning" message="هیچ بخشی یافت نشد!" />
        @endif

</div>
