<div>
    <x-slot name="pageTitle">
        اطلاعات بانکی کارمندان
    </x-slot>
    
    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#add-bank-account-modal">
        اضافه کردن حساب بانکی
    </x-button>
    
    <x-modals.modal id="add-bank-account-modal" title="وارد کردن اطلاعات بانکی کارمندان">

        <x-forms.group for="employee" label="انتخاب کارمند">
            <select wire:model="employee" id="employee" class="form-control rounded">
                <option selected>انتخاب کنید</option>
                @forelse ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->fname . ' ' . $employee->lname }}</option>
                @empty
                    <option disabled>کارمندی تعریف نشده است</option>
                @endforelse
            </select>
            @error('employee')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="bank_name" label="نام بانک">
            <x-forms.input wire:model="bank_name" id="bank_name" />
            @error('bank_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="shaba_num" label="شماره شبا">
            <x-forms.input type="shaba_num" wire:model="shaba_num" id="shaba_num" />
            @error('shaba_num')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="card_num" label="شماره کارت">
            <x-forms.input type="card_num" wire:model="card_num" id="card_num" />
            @error('card_num')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    <x-forms.search-box wire:model="search"></x-forms.search-box>

    @if ($bankAccounts->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام پرسنل</th>
                <th>نام بانک</th>
                <th>شماره شبا</th>
                <th>شماره کارت</th>
                <th>مدیریت</th>
            </x-slot:headers>
            @foreach ($bankAccounts as $account)
                <tr>
                    <td>{{ $account->id }}</td>
                    <td>{{ $account->bankable->fname . ' ' . $account->bankable->lname }}</td>
                    <td>{{ $account->bank_name }}</td>
                    <td>{{ $account->shaba_num }}</td>
                    <td>{{ $account->card_num }}</td>
                    <td>
                        <x-button color="primary" data-bs-toggle="modal" data-bs-target="#edit-bank-account-modal-{{$account->id}}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" class="confirm" id="{{ $account->id }}" title="deleteBankAccount">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
                <livewire:employees.edit.bank :account="$account" :wire:key="'bank-account-'.$account->id">
            @endforeach
        </x-table>
    @else
        <x-alert :message="'هیچ حساب بانکی ثبت نشده است'" type="warning" />
    @endif
</div>
