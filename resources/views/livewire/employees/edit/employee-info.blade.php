<div>
    <x-modal id="edit-employee-modal-{{ $employee->id }}" title="ویرایش اطلاعات کارمند">
        <div class="row">
            <div class="col-sm-6">
                <x-form.input name="fname" label="نام" />

                <x-form.input name="lname" label="نام خانوادگی" />

                <div class="form-group row">
                    <label for="hometown" class="col-sm-4 col-form-label">محل تولد</label>
                    <div class="col-sm-8">
                        <select id="hometown" wire:model="hometown" class="form-control">
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
                            <option @selected($employee->hometown == 'کهگیلویه و بویراحمد') value="کهگیلویه و بویراحمد">کهگیلویه و بویراحمد
                            </option>
                            <option @selected($employee->hometown == 'یزد') value="یزد">یزد</option>
                        </select>
                        @error('hometown')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="gender" class="col-sm-4 col-form-label">جنسیت</label>
                    <div class="col-sm-8">
                        <select id="gender" wire:model="gender" class="form-control">
                            <option @selected($employee->gender == 'male') value="male">مرد</option>
                            <option @selected($employee->gender == 'female') value="female">زن</option>
                        </select>
                        @error('gender')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <x-form.input name="home_phone" label="تلفن منزل" />

                <x-form.input name="address" label="آدرس" />

                <x-form.input name="entry_date" label="تاریخ ورود" />
            </div>

            <div class="col-sm-6">
                <x-form.input name="lname" label="نام خانوادگی" />

                <x-form.input name="birthdate" label="تاریخ تولد" />

                <x-form.input name="father_name" label="نام پدر" />

                <div class="form-group row">
                    <label for="marriage_status" class="col-sm-4 col-form-label">وضیعت تاهل</label>
                    <div class="col-sm-8">
                        <select id="marriage_status" wire:model="marriage_status" class="form-control">
                            <option @selected($employee->gender == 'single') value="single">مجرد</option>
                            <option @selected($employee->gender == 'married') value="married">متاهل</option>
                        </select>
                        @error('marriage_status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <x-form.input name="phone" label="تلفن همراه" />

                <x-form.input type="email" name="email" label="ایمیل" />
            </div>
        </div>
        <x-form.verification />
        <x-slot:footer>
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
