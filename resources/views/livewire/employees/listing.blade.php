<div>
    {{-- The Master doesn't talk, he acts. --}}
    <x-forms.search-box wire:model="search"></x-forms.search-box>

    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#create-employee-modal">تعریف کارمند</x-buttons.btn-success>

    <x-modals.modal id="create-employee-modal" title="تعریف کارمند">
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
                        <option selected>انتخاب کنید</option>
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
                    </x-forms.select>
                    @error('hometown')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <x-forms.group for="gender" label="جنسیت">
                    <x-forms.select id="gender" wire:model="gender" >
                        <option selected >انتخاب کنید</option>
                        <option value="male">مرد</option>
                        <option value="female">زن</option>
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
                    <x-forms.input id="birthdate" wire:model="birthdate"/>
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
                        <option selected>انتخاب کنید</option>
                        <option value="single">مجرد</option>
                        <option value="married">متاهل</option>
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
        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-success wire:click="saveEmployee">ثبت</x-buttons.btn-success>
        </x-slot:footer>
    </x-modals.modal>

    @if ($employees->count() > 0)
        <x-tables.table>
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
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->fname }}</td>
                    <td>{{ $employee->lname }}</td>
                    <td>{{ $employee->melli_code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($employee->birthdate)->format('Y/d/m') }}</td>
                    <td>{{ $employee->hometown }}</td>
                    <td>{{ $employee->father_name }}</td>
                    <td>
                    @switch($employee->gender)
                        @case("male")
                        مرد
                            @break
                        @case("female")
                        زن
                            @break
                        @default

                    @endswitch
                    </td>
                    <td>
                    @switch($employee->marriage_status)
                        @case("single")
                        مجرد
                            @break
                        @case("married")
                        متاهل
                            @break
                        @default

                    @endswitch
                    </td>
                    <td>{{ $employee->home_phone }}</td>
                    <td>{{ $employee->phone }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->address }}</td>
                    <td>{{ $employee->employee_code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($employee->entry_date)->format('Y/d/m') }}</td>
                    <td>
                        <x-buttons.btn-danger wire:click="delete({{ $employee->id }})">حذف</x-buttons.btn-danger>
                        <x-buttons.btn-success data-bs-target="#edit-employee-modal-{{$employee->id}}" data-bs-toggle="modal">ویرایش</x-buttons.btn-success>

                    </td>
                </tr>
                @livewire('employees.edit.employee-info', ['employee' => $employee], key('employee-'.$employee->id))
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>کارمندی تعریف نشده است</x-alerts.danger>
    @endif
</div>
