<div>
    <x-modals.modal id="create-prizes-modal-{{ $key }}" title="تعریف جوایز سطح">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <div class="row">
            <div class="col-md-6">
                <x-forms.group for="psc-{{$level->id}}" label="PSC">
                    <x-forms.input id="psc-{{$level->id}}" wire:model="psc" />
                    @error('psc')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="blue-{{$level->id}}" label="رنگ آبی">
                    <x-forms.input id="blue-{{$level->id}}" wire:model="blue" />
                    @error('blue')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="red-{{$level->id}}" label="رنگ قرمز">
                    <x-forms.input id="red-{{$level->id}}" wire:model="red" />
                    @error('red')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="yellow-{{$level->id}}" label="رنگ زرد">
                    <x-forms.input id="yellow-{{$level->id}}" wire:model="yellow" />
                    @error('yellow')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="union_license-{{$level->id}}" label="مجوز تاسیس اتحاد">
                    <x-forms.select wire:model="union_license" id="union_license-{{$level->id}}">
                        <option selected value="0">خیر</option>
                        <option value="1">بله</option>
                    </x-forms.select>
                    @error('union_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="union_members_count-{{$level->id}}" label="تعداد عضوگیری مجاز در اتحاد">
                    <x-forms.input id="union_members_count-{{$level->id}}" wire:model="union_members_count" />
                    @error('union_members_count')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="observing_license-{{$level->id}}" label="مجوز بازرسی">
                    <x-forms.select wire:model="observing_license" id="observing_license-{{$level->id}}">
                        <option selected value="0">خیر</option>
                        <option value="1">بله</option>
                    </x-forms.select>
                    @error('observing_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="gate_license-{{$level->id}}" label="مجوز دروازه">
                    <x-forms.select wire:model="gate_license" id="gate_license-{{$level->id}}">
                        <option selected value="0">خیر</option>
                        <option value="1">بله</option>
                    </x-forms.select>
                    @error('gate_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            <div class="col-md-6">

                <x-forms.group for="lawyer_license-{{$level->id}}" label="مجوز وکالت">
                    <x-forms.select wire:model="lawyer_license" id="lawyer_license-{{$level->id}}">
                        <option selected value="0">خیر</option>
                        <option value="1">بله</option>
                    </x-forms.select>
                    @error('lawyer_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="city_counsile_entry-{{$level->id}}" label="ورود به شورای شهر">
                    <x-forms.select wire:model="city_counsile_entry" id="city_counsile_entry-{{$level->id}}">
                        <option selected value="0">خیر</option>
                        <option value="1">بله</option>
                    </x-forms.select>
                    @error('city_counsile_entry')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="special_residence_property-{{$level->id}}" label="ملک مسکونی ویژه">
                    <x-forms.input id="special_residence_property-{{$level->id}}" wire:model="special_residence_property" />
                    @error('special_residence_property')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="property_on_area-{{$level->id}}" label="ملک روی سطح">
                    <x-forms.input id="property_on_area-{{$level->id}}" wire:model="property_on_area" />
                    @error('property_on_area')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="judge_entry-{{$level->id}}" label="ورود به قضاوت">
                    <x-forms.select wire:model="judge_entry" id="judge_entry-{{$level->id}}">
                        <option selected value="0">انتخاب کنید</option>
                        <option value="1">دادگاه عمومی</option>
                        <option value="2">دادگاه کیفری</option>
                    </x-forms.select>
                    @error('judge_entry')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="satisfaction-{{$level->id}}" label="رضایت">
                    <x-forms.input id="satisfaction-{{$level->id}}" wire:model="satisfaction" />
                    @error('satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="effect-{{$level->id}}" label="تاثیرگذاری در رای">
                    <x-forms.input id="effect-{{$level->id}}" wire:model="effect" />
                    @error('effect')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-slot:footer>
                    <x-buttons.btn-primary wire:click="save">ثبت</x-buttons.btn-primary>
                    <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
                </x-slot:footer>
            </div>
        </div>
    </x-modals.modal>
</div>
