<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-modals.modal id="edit-employee-modal-{{$employee->id}}" title="ویرایش اطلاعات کارمند">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-6">
                <x-forms.group for="fname" label="نام">
                    <x-forms.input id="fname" wire:model="fname" />
                    @error('fname')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="melli_code" label="کد ملی">
                    <x-forms.input id="melli_code" wire:model="melli_code" />
                    @error('melli_code')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="hometown" label="محل تولد">
                    <x-forms.select id="hometown" wire:model="hometown">
                        <option @selected($employee->hometown == 'قزوین') value="قزوین">قزوین</option>
                        <option @selected($employee->hometown == 'البرز') value="البرز">البرز</option>
                        <option @selected($employee->hometown == 'تهران') value="تهران">تهران</option>
                        <option @selected($employee->hometown == 'قم') value="قم">قم</option>
                        <option @selected($employee->hometown == 'کرمان') value="کرمان">کرمان</option>
                        <option @selected($employee->hometown == 'کرمانشاه') value="کرمانشاه">کرمانشاه</option>
                        <option @selected($employee->hometown == 'اهواز') value="اهواز">اهواز</option>
                        <option @selected($employee->hometown == 'اراک') value="اراک">اراک</option>
                        <option @selected($employee->hometown == 'زنجان') value="زنجان">زنجان</option>
                        <option @selected($employee->hometown == 'خراسان رضوی') value="خراسان رضوی">خراسان رضوی</option>
                        <option @selected($employee->hometown == 'آذربایجان غربی') value="آذربایجان غربی">آذربایجان غربی</option>
                        <option @selected($employee->hometown == 'آذربایجان شرقی') value="آذربایجان شرقی">آذربایجان شرقی</option>
                        <option @selected($employee->hometown == 'گیلان') value="گیلان">گیلان</option>
                        <option @selected($employee->hometown == 'مازندران') value="مازندران">مازندران</option>
                        <option @selected($employee->hometown == 'همدان') value="همدان">همدان</option>
                        <option @selected($employee->hometown == 'اصفهان') value="اصفهان">اصفهان</option>
                        <option @selected($employee->hometown == 'خوزستان') value="خوزستان">خوزستان</option>
                        <option @selected($employee->hometown == 'ایلام') value="ایلام">ایلام</option>
                        <option @selected($employee->hometown == 'خراسان شمالی') value="خراسان شمالی">خراسان شمالی</option>
                        <option @selected($employee->hometown == 'هرمزگان') value="هرمزگان">هرمزگان</option>
                        <option @selected($employee->hometown == 'بوشهر') value="بوشهر">بوشهر</option>
                        <option @selected($employee->hometown == 'خراسان جنوبی') value="حراسان جنوبی">خراسان جنوبی</option>
                        <option @selected($employee->hometown == 'لرستان') value="لرستان">لرستان</option>
                        <option @selected($employee->hometown == 'سمنان') value="سمنان">سمنان</option>
                        <option @selected($employee->hometown == 'کردستان') value="کردستان">کردستان</option>
                        <option @selected($employee->hometown == 'چهارمحال و بختیاری') value="چهارمحال و بختیاری">چهارمحال و بختیاری</option>
                        <option @selected($employee->hometown == 'فارس') value="فارس">فارس</option>
                        <option @selected($employee->hometown == 'گلستان') value="گلستان">گلستان</option>
                        <option @selected($employee->hometown == 'کهگیلویه و بویراحمد') value="کهگیلویه و بویراحمد">کهگیلویه و بویراحمد</option>
                        <option @selected($employee->hometown == 'یزد') value="یزد">یزد</option>
                    </x-forms.select>
                    @error('hometown')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="gender" label="جنسیت">
                    <x-forms.select id="gender" wire:model="gender">
                        <option @selected($employee->gender == 'male') value="male">مرد</option>
                        <option @selected($employee->gender == 'female') value="female">زن</option>
                    </x-forms.select>
                    @error('gender')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="home_phone" label="تلفن ثابت">
                    <x-forms.input id="home_phone" wire:model="home_phone" />
                    @error('home_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="address" label="آدرس">
                    <x-forms.input id="address" wire:model="address" />
                    @error('address')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="entry_date" label="تاریخ ورود">
                    <x-forms.input id="entry_date" wire:model="entry_date" />
                    @error('entry_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>

            <div class="col-sm-6">
                <x-forms.group for="lname" label="نام خانوادگی">
                    <x-forms.input id="lname" wire:model="lname" />
                    @error('lname')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="birthdate" label="تاریخ تولد">
                    <x-forms.input id="birthdate" wire:model="birthdate" />
                    @error('birthdate')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="father_name" label="نام پدر">
                    <x-forms.input id="father_name" wire:model="father_name" />
                    @error('father_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="marriage_status" label="وضیعت تاهل">
                    <x-forms.select id="marriage_status" wire:model="marriage_status">
                        <option @selected($employee->gender == 'single') value="single">مجرد</option>
                        <option @selected($employee->gender == 'married') value="married">متاهل</option>
                    </x-forms.select>
                    @error('marriage_status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="phone" label="تلفن همراه">
                    <x-forms.input id="phone" wire:model="phone" />
                    @error('phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="email" label="ایمیل">
                    <x-forms.input id="email" wire:model="email" />
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
        </div>
        <x-forms.verification id="{{ $employee->id }}"/>
        <x-slot:footer>
            <x-buttons.btn-success wire:click="save">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
