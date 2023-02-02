<div>
    <x-modals.modal size="modal-xl" id="edit-limit-modal-{{ $limit->id }}" title="ویرایش محدودیت املاک">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="title" label="عنوان">
            <x-forms.input wire:model="title" id="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="starting_id" label="شناسه شروع">
            <x-forms.input wire:model="startingId" id="starting_id" />
            @error('startingId')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="ending_id" label="شناسه پایانی">
            <x-forms.input wire:model="endingId" id="ending_id" />
            @error('endingId')
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
                    <div class="col-8">محدود شده به قیمت ثابت</div>
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i>PSC</i>
                            </span>
                            <x-forms.input wire:model="price"/>
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">محدودیت تعداد خرید زمین( هر نفر)</div>
                    <div class="col-4">
                        <x-forms.input wire:model="buyCountLimitForEachIndividual" />
                        @error('buyCountLimitForEachIndividual')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">تاریخ شروع</div>
                    <div class="col-4">
                        <x-forms.input wire:model="startingDate" />
                        @error('startingDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-8">تاریخ پایان</div>
                    <div class="col-4">
                        <x-forms.input wire:model="endingDate" />
                        @error('endingDate')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">
                    ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="code" placeholder="تایید پیامکی" />
                @error('code')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <x-forms.group label="رمز دسترسی" for="accessPassword">
            <x-forms.input type="password" id="accessPassword" wire:model="accessPassword" placeholder="رمز دسترسی" />
            @error('accessPassword')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
