<div>
    <x-button color="primary" class="my-2" data-bs-toggle="modal" data-bs-target="#add-bank-account-modal">
        اضافه کردن حساب بانکی
    </x-button>

    <x-modal id="add-bank-account-modal" title="وارد کردن اطلاعات بانکی کارمندان">

        <div class="form-group row">
            <label for="employee" class="col-sm-4 col-form-label">انتخاب کارمند</label>
            <div class="col-sm-8">
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
            </div>
        </div>

        <x-form.input name="bank_name" label="نام بانک" />

        <x-form.input name="shaba_num" label="شماره شبا" />

        <x-form.input name="card_num" label="شماره کارت" />

        <x-form.verification/>

        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    <x-form.search-box wire:model="search" />

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
                <tr wire:key="{{ $account->id }}">
                    <td>{{ $account->id }}</td>
                    <td>{{ $account->bankable->fname . ' ' . $account->bankable->lname }}</td>
                    <td>{{ $account->bank_name }}</td>
                    <td>{{ $account->shaba_num }}</td>
                    <td>{{ $account->card_num }}</td>
                    <td>
                        <x-button color="primary" data-bs-toggle="modal" data-bs-target="#edit-bank-account-modal-{{$account->id}}">
                            <span class="fa fa-edit"></span>
                        </x-button>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟" wire:click="delete({{ $account->id }})">
                            <span class="fa fa-trash"></span>
                        </x-button>
                    </td>
                </tr>
                <livewire:employees.edit.bank :$account :key="$account->id" />
            @endforeach
        </x-table>
        {{ $bankAccounts->links() }}
    @else
        <x-alert :message="'هیچ حساب بانکی ثبت نشده است'" type="warning" />
    @endif
</div>
