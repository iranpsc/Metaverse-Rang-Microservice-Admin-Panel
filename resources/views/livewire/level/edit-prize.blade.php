<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <x-modals.modal id="edit-prizes-modal-{{ $key }}" title="ویرایش جوایز سطح {{ $level->name }}">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <div class="row">
            <div class="col-md-6">
                <x-forms.group for="edit-psc-{{$prize->id}}" label="PSC">
                    <x-forms.input id="edit-psc-{{$prize->id}}" wire:model="psc" />
                    @error('psc')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="edit-blue-{{$prize->id}}" label="رنگ آبی">
                    <x-forms.input id="edit-blue-{{$prize->id}}" wire:model="blue" />
                    @error('blue')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="edit-red-{{$prize->id}}" label="رنگ قرمز">
                    <x-forms.input id="edit-red-{{$prize->id}}" wire:model="red" />
                    @error('red')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="edit-yellow-{{$prize->id}}" label="رنگ زرد">
                    <x-forms.input id="edit-yellow-{{$prize->id}}" wire:model="yellow" />
                    @error('yellow')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="edit-union_license-{{$prize->id}}" label="مجوز تاسیس اتحاد">
                    <x-forms.select wire:model="union_license" id="edit-union_license-{{$prize->id}}">
                        <option @selected($prize->union_licence) value="1">بله</option>
                        <option @selected($prize->union_licence) value="0">خیر</option>
                    </x-forms.select>
                    @error('union_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-union_members_count-{{$prize->id}}" label="تعداد عضوگیری مجاز در اتحاد">
                    <x-forms.input id="edit-union_members_count-{{$prize->id}}" wire:model="union_members_count" />
                    @error('union_members_count')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-observing_license-{{$prize->id}}" label="مجوز بازرسی">
                    <x-forms.select wire:model="observing_license" id="edit-observing_license-{{$prize->id}}">
                        <option @selected($prize->observing_license) value="1">بله</option>
                        <option @selected($prize->observing_license) value="0">خیر</option>
                    </x-forms.select>
                    @error('observing_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-gate_license-{{$prize->id}}" label="مجوز دروازه">
                    <x-forms.select wire:model="gate_license" id="edit-gate_license-{{$prize->id}}">
                        <option @selected($prize->gate_license)  value="1">بله</option>
                        <option @selected($prize->gate_license)  value="0">خیر</option>
                    </x-forms.select>
                    @error('gate_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
                <x-forms.group for="edit-lawyer_license-{{$prize->id}}" label="مجوز وکالت">
                    <x-forms.select wire:model="lawyer_license" id="edit-lawyer_license-{{$prize->id}}">
                        <option @selected($prize->lawyer_license) value="1">بله</option>
                        <option @selected($prize->lawyer_license) value="0">خیر</option>
                    </x-forms.select>
                    @error('lawyer_license')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            <div class="col-md-6">

                <x-forms.group for="edit-city_counsile_entry-{{$prize->id}}" label="ورود به شورای شهر">
                    <x-forms.select wire:model="city_counsile_entry" id="edit-city_counsile_entry-{{$prize->id}}">
                        <option @selected($prize->city_counsile_entry) value="1">بله</option>
                        <option @selected($prize->city_counsile_entry) value="0">خیر</option>
                    </x-forms.select>
                    @error('city_counsile_entry')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="edit-upload-feature-image-{{$level->id}}" label="بار گذاری تصاویر املاک">
                    <x-forms.select wire:model="upload_feature_image" id="edit-upload-feature-image-{{$level->id}}">
                        <option @selected($prize->upload_feature_image) value="0">خیر</option>
                        <option @selected($prize->upload_feature_image) value="1">بله</option>
                    </x-forms.select>
                    @error('upload_feature_image')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="delete-feature-image-{{$level->id}}" label="حذف تصاویر املاک">
                    <x-forms.select wire:model="delete_feature_image" id="delete-feature-image-{{$level->id}}">
                        <option @selected($prize->delete_feature_image) value="0">خیر</option>
                        <option @selected($prize->delete_feature_image) value="1">بله</option>
                    </x-forms.select>
                    @error('delete_feature_image')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-special_residence_property-{{$prize->id}}" label="ملک مسکونی ویژه">
                    <x-forms.input id="edit-special_residence_property-{{$prize->id}}" wire:model="special_residence_property" />
                    @error('special_residence_property')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-property_on_area-{{$prize->id}}" label="ملک روی سطح">
                    <x-forms.input id="edit-property_on_area-{{$prize->id}}" wire:model="property_on_area" />
                    @error('property_on_area')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="edit-judge_entry-{{$prize->id}}" label="ورود به قضاوت">
                    <x-forms.select wire:model="judge_entry" id="edit-judge_entry-{{$prize->id}}">
                        <option @selected($prize->judge_entry) value="0">انتخاب کنید</option>
                        <option @selected($prize->judge_entry) value="1">دادگاه عمومی</option>
                        <option @selected($prize->judge_entry) value="2">دادگاه کیفری</option>
                    </x-forms.select>
                    @error('judge_entry')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="edit-satisfaction-{{$prize->id}}" label="رضایت">
                    <x-forms.input id="edit-satisfaction-{{$prize->id}}" wire:model="satisfaction" />
                    @error('satisfaction')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>


                <x-forms.group for="edit-effect-{{$prize->id}}" label="تاثیرگذاری در رای">
                    <x-forms.input id="edit-effect-{{$prize->id}}" wire:model="effect" />
                    @error('effect')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
        </div>
        <x-slot:footer>
            <x-buttons.btn-primary wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بازگشت</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
