<div>
    <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#create-limit-modal">تعریف محدودیت
    </x-buttons.btn-primary>

    <x-modals.modal size="modal-xl" id="create-limit-modal" title="تعریف محدودیت املاک">
        <x-forms.group for="title" label="عنوان">
            <x-forms.input wire:model="title" id="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="starting_id" label="شناسه شروع">
            <x-forms.input wire:model="startingId" id="starting_id" />
            @error('starting_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="ending_id" label="شناسه پایانی">
            <x-forms.input wire:model="endingId" id="ending_id" />
            @error('ending_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <hr>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">محدودیت تنها با احراز مشخصات فردی</div>
                    <div class="col-1"><input type="checkbox" wire:model="verifiedKycLimit"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">محدودیت خرید تنها سن زیر 18 سال</div>
                    <div class="col-1"><input type="checkbox" wire:model="under18BuyLimit"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">محدودیت تنها با احراز بانکی</div>
                    <div class="col-1"><input type="checkbox" wire:model="verifiedBankAccountLimit"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">محدودیت خرید تنها سن بالای 18 سال</div>
                    <div class="col-1"><input type="checkbox" wire:model="moreThan18BuyLimit"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">قفل غیر قابل فروش</div>
                    <div class="col-1"><input type="checkbox" wire:model="notAllowedToBeSold"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-11">محدودیت خرید تنها دارندگان سلسله</div>
                    <div class="col-1"><input type="checkbox" wire:model="dynastyOwnerBuyLimit"></div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row my-2">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">زمین پیش خرید شده توسط</div>
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i>HM</i>
                            </span>
                            <x-forms.input wire:model="preboughtBy"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">محدودیت تعداد خرید زمین( هر نفر)</div>
                    <div class="col-4">
                        <x-forms.input wire:model="buyCountLimitForEachIndividual"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">تاریخ شروع</div>
                    <div class="col-4">
                        <x-forms.input class="has-persian-datepicker" wire:model="startingDate"/>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">تاریخ پایان</div>
                    <div class="col-4">
                        <x-forms.input class="has-persian-datepicker" wire:model="endingDate"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">محدود شده به قیمت ثابت</div>
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i>PSC</i>
                            </span>
                            <x-forms.input wire:model="fixedPscPrice"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-sm-4">
                <x-buttons.btn-success>ارسال کد تایید</x-forms.button-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input />
            </div>
        </div>
        <x-forms.group for="accessPassword" label="رمز دسترسی">
            <x-forms.input />
            @error('accessPassword')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success>ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    {{-- @if ($limits->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>مساحت</th>
                <th>تراکم</th>
                <th>نوع کاربری</th>
                <th>آدرس</th>
                <th>تاریخ ثبت</th>
                <th>ثبت کننده</th>
                <th>ملاحضات</th>
            </x-slot:headers>
        </x-tables.table>
        {{ $limits->links() }}
    @else
        <x-alerts.danger>محدودیتی ثبت نشده است.</x-alerts.danger>
    @endif --}}
</div>
