<div>
    <x-buttons.btn-primary class="mb-2" data-bs-toggle="modal" data-bs-target="#create-category-modal">ایجاد دسته
        بندی</x-buttons.btn-primary>
    <x-modals.modal title="ایجاد دسته بندی" id="create-category-modal">
        <x-forms.group for="parentCategory" label="انتخاب دسته بندی پدر">
            <x-forms.select id="parentCategory" wire:model="parentCategory">
                @if ($categories->count() > 0)
                    <option value="" selected>انتخاب کنید</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                @else
                    <option value="" selected>دسته بندی تعریف نشده است.</option>
                @endif
            </x-forms.select>
            @error('category')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="name" label="نام دسته بندی">
            <x-forms.input wire:model.lazy="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="slug" label="نامک">
            <x-forms.input wire:model.lazy="slug" />
            @error('slug')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="description" label="توضیحات">
            <textarea wire:model.lazy="description" class="form-control rounded" id="description" cols="30" rows="10"></textarea>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="image" label="تصویر">
            <x-forms.input type="file" wire:model.lazy="image" />
            <x-progress-bar/>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:click="save" wire:loading.attr="disabled">ثبت</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($categories->count() > 0)
        <ul class="nav nav-tabs">
            @foreach ($categories as $index => $category)
                <li class="nav-item">
                    <a class="nav-link @if ($index == 0) active @endif" href="{{ '#tab' . $index + 1 }}"
                        data-bs-toggle="tab">
                        <span>
                            {{ $category->name }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach ($categories as $index => $category)
                <div class="tab-pane fade @if ($index == 0) active show @endif"
                    id="{{ 'tab' . $index + 1 }}">
                    <x-tables.table>
                        <x-slot name="headers">
                            <th>نام</th>
                            <th>نامک</th>
                            <th>تصویر</th>
                            <th>تاریخ ایجاد</th>
                            <th>ساعت ایجاد</th>
                            <th>مدیریت</th>
                        </x-slot>
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td><a href="{{ $category->image }}" target="_blank"
                                    class="btn btn-primary btn-sm round">مشاهده</a></td>
                            <td>{{ jdate($category->created_at)->format('Y/m/d') }}
                            </td>
                            <td>{{ jdate($category->created_at)->format('H:m:s') }}
                            </td>
                            <td>
                                <x-buttons.btn-primary data-bs-toggle="modal"
                                    data-bs-target="#edit-category-modal-{{ $category->id }}">ویرایش
                                    </x-button.btn-primary>
                                    <x-buttons.btn-danger class="confirm" title="deleteVideoCategory"
                                        id="{{ $category->id }}">حذف</x-buttons.btn-danger>
                                <livewire:videos.edit-category :category="$category" :wire:key="'edit-category-'.$category->id">
                            </td>
                        </tr>
                    </x-tables.table>
                    @if ($category->subCategories->count() > 0)
                        <p class="alert alert-info">زیر دسته ها</p>
                        <x-tables.table>
                            <x-slot name="headers">
                                <th>نام</th>
                                <th>نامک</th>
                                <th>تصویر</th>
                                <th>تاریخ ایجاد</th>
                                <th>ساعت ایجاد</th>
                                <th>مدیریت</th>
                            </x-slot>
                            @foreach ($category->subCategories as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->slug }}</td>
                                    <td><a href="{{ $item->image }}" target="_blank"
                                            class="btn btn-primary btn-sm round">مشاهده</a></td>
                                    <td>{{ \Morilog\Jalali\Jalalian::forge($item->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td>{{ \Morilog\Jalali\Jalalian::forge($item->created_at)->format('H:m:s') }}
                                    </td>
                                    <td>

                                        <x-buttons.btn-primary data-bs-toggle="modal"
                                            data-bs-target="#edit-sub-category-modal-{{ $item->id }}">ویرایش
                                            </x-button.btn-primary>
                                            <x-buttons.btn-danger class="confirm" title="deleteVideoSubCategory"
                                                id="{{ $item->id }}">حذف</x-buttons.btn-danger>
                                        <livewire:videos.edit-sub-category :subCategory="$item" :wire:key="'edit-sub-category-'.$item->id">
                                    </td>
                                </tr>
                            @endforeach
                        </x-tables.table>
                    @else
                        <x-alerts.danger>زیر دسته ای تعریف نشده است.</x-alerts.danger>
                    @endif
                </div>
            @endforeach
        </div>
        {{ $categories->links() }}
    @else
        <x-alerts.danger>دسته بندی تعریف نشده است.</x-alerts.danger>
    @endif
</div>
