<div>
    <div class="row">
        <div class="col-sm-4">
            <x-button data-bs-toggle="modal" data-bs-target="#create-modal" class="mb-2">
                <span class="fa fa-plus"></span>
                ایجاد کد ISIC
            </x-button>
        </div>
        <div class="col-sm-4">
            <x-button data-bs-toggle="modal" data-bs-target="#import-modal" class="mb-2">
                <span class="fa fa-plus"></span>
                درون ریزی
            </x-button>
        </div>
        <div class="col-sm-4">
            <input type="text" wire:model="search" class="form-control rounded" placeholder="جستجو..." />
        </div>
    </div>

    <x-modal id="import-modal" title="درون ریزی کد ISIC">
        <p>درون ریزی کد ISIC:</p>
        <x-form.input type="file" name="import" label="فایل" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:click="import">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modal>

    <x-modal id="create-modal" title="ایجاد کد ISIC">

        <x-form.input name="name" label="نام" />

        <x-form.input name="code" label="کد" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modal>

    @if ($isic_codes->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نام</th>
                <th>کد</th>
                <th>وضعیت</th>
                <th>اقدامات</th>
            </x-slot>

            @foreach ($isic_codes as $isic_code)
                <tr wire:key="{{ $isic_code->id }}" >
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $isic_code->name }}</td>
                    <td>{{ $isic_code->code }}</td>
                    <td>{{ $isic_code->verified ? 'تایید شده' : 'در انتظار تایید' }}</td>
                    <td>
                        @unless ($isic_code->verified)
                            <x-button color="success" wire:confirm="آیا می خواهید تایید کنید؟" wire:click="approve({{ $isic_code->id }})">
                                <span class="fa fa-check"></span>
                                تایید
                            </x-button>
                            <x-button color="danger" wire:confirm="آیا می خواهید رد کنید؟" wire:click="deny({{ $isic_code->id }})">
                                <span class="fa fa-times"></span>
                                تایید
                            </x-button>
                        @endunless
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟"  wire:click="delete({{ $isic_code->id }})">
                            <span class="fa fa-trash"></span>
                            حذف
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $isic_codes->links() }}
    @else
        <x-alert type="warning" message="داده ای ثبت نشده است." />
    @endif
</div>
