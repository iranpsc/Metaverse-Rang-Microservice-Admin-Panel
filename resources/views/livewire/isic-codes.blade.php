<div>
    <x-slot name="pageTitle">
        کدهای ISIC
    </x-slot>

    <div class="row">
        <div class="col-sm-6">
            <x-button data-bs-toggle="modal" data-bs-target="#modal" class="mb-2">
                <span class="fa fa-plus"></span>
                ایجاد کد ISIC
            </x-button>
        </div>
        <div class="col-sm-6">
            <input type="text" wire:model="search" class="form-control rounded" placeholder="جستجو..." />
        </div>
    </div>



    <x-modal id="modal" title="ایجاد کد ISIC">

        @if(!$is_editing)
            <p>درون ریزی:</p>
            <x-forms.group for="import" label="فایل">
                <x-forms.input id="import" type="file" wire:model="import" />
                @error('import')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>

            <p>ایجاد کد ISIC جدید:</p>
        @endif

        <x-forms.group for="name" label="نام">
            <x-forms.input id="name" wire:model="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="code" label="کد">
            <x-forms.input id="code" wire:model="code" />
            @error('code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        @if ($is_editing)
            <x-forms.group for="verified" label="تایید می کنید؟">
                <x-forms.select id="verified" wire:model="verified">
                    <option value="1">بله</option>
                    <option value="0">خیر</option>
                </x-forms.select>
                @error('verified')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        @endif

        @production
            <x-forms.verification />
        @endproduction
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
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $isic_code->name }}</td>
                    <td>{{ $isic_code->code }}</td>
                    <td>{{ $isic_code->verified ? 'تایید شده' : 'در انتظار تایید' }}</td>
                    <td>
                        <x-button color="info" id="edit-btn-{{ $isic_code->id }}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" id="delete-btn-{{ $isic_code->id }}">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $isic_codes->links() }}
    @else
        <x-alert type="warning" message="داده ای ثبت نشده است." />
    @endif

    <x-scripts />
</div>
