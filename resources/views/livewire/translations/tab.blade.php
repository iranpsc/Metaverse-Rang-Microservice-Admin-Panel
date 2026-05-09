<div>

    <x-breadcrumb>
        <x-breadcrumb.item title="ترجمه ها" href="{{ route('translations') }}" />
        <x-breadcrumb.item title="بخش ها" href="{{ route('modals', $modal->translation->id) }}" />
        <x-breadcrumb.item title="تب ها" active="true" />
    </x-breadcrumb>
    <br>

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-tab">ایجاد تب جدید</x-button>

    <x-modal id="create-tab" title="ایجاد تب جدید">
        <x-form.input name="name" label="نام تب" />
        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($tabs->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام بخش</th>
                <th>پیشرفت</th>
                <th>تعداد</th>
                <th>انجام شده</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($tabs as $tab)
                <tr wire:key="{{ $tab->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $tab->name }}</td>
                    <td>
                        <div class="progress">
                            @php
                                $tab->progress =
                                    $tab->fields_count > 0
                                        ? round(($tab->translated_fields_count / $tab->fields_count) * 100)
                                        : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $tab->progress }}%"
                                aria-valuenow="{{ $tab->progress }}" aria-valuemin="0" aria-valuemax="100">
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
                            'tab' => $tab->id,
                        ]) }}"
                            class="btn btn-primary rounded"><span class="fa fa-edit"></span></a>
                        <x-button color="info" data-bs-toggle="modal" data-bs-target="#edit-tab-{{ $tab->id }}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" wire:confirm="ایا می خواهید تب را حذف کنید؟"
                            wire:click="delete({{ $tab->id }})">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
                <livewire:translations.edit-tab :$tab :key="$tab->id" />
            @endforeach
        </x-table>
        {{ $tabs->links() }}
    @else
        <x-alert type="warning" message="هیچ تبی یافت نشد!" />
    @endif
</div>
