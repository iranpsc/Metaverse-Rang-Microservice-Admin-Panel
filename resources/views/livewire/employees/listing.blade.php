<div>
    <x-form.search-box wire:model.live="search" />

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#create-employee-modal">تعریف کارمند</x-button>

    <x-modal id="create-employee-modal" title="تعریف کارمند">
        <div class="row">
            <div class="col-sm-6">
                <x-form.input name="fname" label="نام" />

                <x-form.input name="lname" label="نام خانوادگی" />

                <x-form.input name="melli_code" label="کد ملی" />

                <div class="form-group row">
                    <label for="hometown" class="form-col-label col-sm-4">محل تولد</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded" id="hometown" wire:model="hometown">
                            <option selected value="">انتخاب کنید</option>
                            <option value="قزوین">قزوین</option>
                            <option value="البرز">البرز</option>
                            <option value="تهران">تهران</option>
                            <option value="قم">قم</option>
                            <option value="کرمان">کرمان</option>
                            <option value="کرمانشاه">کرمانشاه</option>
                            <option value="اهواز">اهواز</option>
                            <option value="اراک">اراک</option>
                            <option value="زنجان">زنجان</option>
                            <option value="خراسان رضوی">خراسان رضوی</option>
                            <option value="آذربایجان غربی">آذربایجان غربی</option>
                            <option value="آذربایجان شرقی">آذربایجان شرقی</option>
                            <option value="گیلان">گیلان</option>
                            <option value="مازندران">مازندران</option>
                            <option value="همدان">همدان</option>
                            <option value="اصفهان">اصفهان</option>
                            <option value="خوزستان">خوزستان</option>
                            <option value="ایلام">ایلام</option>
                            <option value="خراسان شمالی">خراسان شمالی</option>
                            <option value="هرمزگان">هرمزگان</option>
                            <option value="بوشهر">بوشهر</option>
                            <option value="حراسان جنوبی">خراسان جنوبی</option>
                            <option value="لرستان">لرستان</option>
                            <option value="سمنان">سمنان</option>
                            <option value="کردستان">کردستان</option>
                            <option value="چهارمحال و بختیاری">چهارمحال و بختیاری</option>
                            <option value="فارس">فارس</option>
                            <option value="گلستان">گلستان</option>
                            <option value="کهگیلویه و بویراحمد">کهگیلویه و بویراحمد</option>
                            <option value="یزد">یزد</option>
                        </select>
                        @error('hometown')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="gender" class="form-col-label col-sm-4">جنسیت</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded" id="gender" wire:model="gender">
                            <option selected>انتخاب کنید</option>
                            <option value="male">مرد</option>
                            <option value="female">زن</option>
                        </select>
                        @error('gender')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <x-form.input name="home_phone" label="تلفن ثابت" />

                <x-form.input name="address" label="آدرس" />

            </div>

            <div class="col-sm-6">

                <x-form.input name="entry_date" label="تاریخ ورود" wire:model="entry_date" />

                <x-form.input name="birthdate" label="تاریخ تولد" wire:model="birthdate" />

                <x-form.input name="father_name" label="نام پدر" wire:model="father_name" />

                <div class="form-group row">
                    <label for="marriage_status" class="form-col-label col-sm-4">وضعیت تاهل</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded" id="marriage_status" wire:model="marriage_status">
                            <option selected>انتخاب کنید</option>
                            <option value="single">مجرد</option>
                            <option value="married">متاهل</option>
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
            <x-button color="success" wire:loading.attr="disabled" wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modal>

    @if ($employees->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>کدملی</th>
                <th>تاریخ تولد</th>
                <th>محل تولد</th>
                <th>نام پدر</th>
                <th>جنسیت</th>
                <th>وضعیت تاهل</th>
                <th>تلفن ثابت</th>
                <th>تلفن همراه</th>
                <th>ایمیل</th>
                <th>آدرس</th>
                <th>شناسه کارمندی</th>
                <th>تاریخ ورود</th>
                <th>مدیریت</th>
            </x-slot:headers>

            @foreach ($employees as $employee)
                <tr wire:key="{{ $employee->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->fname }}</td>
                    <td>{{ $employee->lname }}</td>
                    <td>{{ $employee->melli_code }}</td>
                    <td>{{ $employee->birthdate }}</td>
                    <td>{{ $employee->hometown }}</td>
                    <td>{{ $employee->father_name }}</td>
                    <td>
                        {{ $employee->male ? 'مرد' : 'زن' }}
                    </td>
                    <td>
                        {{ $employee->marriage_status == 'married' ? 'متاهل' : 'مجرد' }}
                    </td>
                    <td>{{ $employee->home_phone }}</td>
                    <td>{{ $employee->phone }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->address }}</td>
                    <td>{{ $employee->employee_code }}</td>
                    <td>{{ $employee->entry_date }}</td>
                    <td>
                        <x-button data-bs-target="#edit-employee-modal-{{ $employee->id }}"
                            data-bs-toggle="modal">ویرایش</x-button>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟" wire:click="delete({{ $employee->id }})">حذف</x-button>
                    </td>
                </tr>
                <livewire:employees.edit.employee-info :$employee :key="$employee->id" />
            @endforeach
        </x-table>
    @else
        <x-alert type="danger" :message="'کارمندی یافت نشد'" />
    @endif
</div>
