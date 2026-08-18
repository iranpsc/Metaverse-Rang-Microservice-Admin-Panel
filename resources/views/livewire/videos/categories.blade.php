<div>
    <x-button class="mb-2" data-bs-toggle="modal" data-bs-target="#create-category-modal">ایجاد دسته بندی</x-button>

    <x-form.search-box wire:model="search" />

    <x-modal size="modal-xl" title="ایجاد دسته بندی" id="create-category-modal">
        <div class="row form-group">
            <label for="parentCategory" class="col-sm-4 col-form-label">انتخاب دسته بندی پدر</label>
            <div class="col-sm-8">
                <select id="parentCategory" wire:model="parentCategory" class="form-control rounded">
                    @if ($categories->count() > 0)
                        <option value="" selected>انتخاب کنید</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    @else
                        <option value="" selected>دسته بندی تعریف نشده است.</option>
                    @endif
                </select>
                @error('category')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input name="name" label="نام" />

        <x-form.input name="slug" label="نامک" />

        <div class="form-group">
            <label for="description">توضیحات</label>
            <div wire:ignore>
                <textarea id="description" class="form-control"></textarea>
            </div>
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <x-form.input type="file" name="image" label="تصویر" />

        <x-form.input type="file" name="icon" label="آیکون" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button id="save-btn">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($categories->count() > 0)
        <ul class="nav nav-tabs">
            @foreach ($categories as $index => $category)
                <li class="nav-item" wire:key="{{ $category->id }}">
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
                    id="{{ 'tab' . $index + 1 }}" wire:key="{{ $category->id }}">
                    <x-table>
                        <x-slot name="headers">
                            <th>نام</th>
                            <th>نامک</th>
                            <th>تصویر</th>
                            <th>آیکون</th>
                            <th>تاریخ ایجاد</th>
                            <th>ساعت ایجاد</th>
                            <th>مدیریت</th>
                        </x-slot>
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td><a href="{{ asset('uploads/' . $category->image) }}" target="_blank"
                                    class="btn btn-primary btn-sm round">مشاهده</a></td>
                            <td><a href="{{ asset('uploads/' . $category->icon) }}" target="_blank"
                                    class="btn btn-primary btn-sm round">مشاهده</a></td>
                            <td>{{ jdate($category->created_at)->format('Y/m/d') }}
                            </td>
                            <td>{{ jdate($category->created_at)->format('H:m:s') }}
                            </td>
                            <td>
                                <x-button data-bs-toggle="modal" data-bs-target="#edit-category-modal-{{ $category->id }}">ویرایش</x-button>

                                <x-button color="danger" wire:confirm="آیا از حذف این دسته بندی اطمینان دارید؟" wire:click="deleteCategory({{ $category->id }})">حذف</x-button>

                                <livewire:videos.edit-category :category="$category" :key="'edit-category-'.$category->id">
                            </td>
                        </tr>
                    </x-table>
                    @if ($category->subCategories->count() > 0)
                        <p class="alert alert-info">زیر دسته ها</p>
                        <x-table>
                            <x-slot name="headers">
                                <th>نام</th>
                                <th>نامک</th>
                                <th>تصویر</th>
                                <th>آیکون</th>
                                <th>تاریخ ایجاد</th>
                                <th>ساعت ایجاد</th>
                                <th>مدیریت</th>
                            </x-slot>
                            @foreach ($category->subCategories as $item)
                                <tr wire:key="{{ $item->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->slug }}</td>
                                    <td><a href="{{ asset('uploads/' . $item->image) }}" target="_blank"
                                            class="btn btn-primary btn-sm round">مشاهده</a></td>
                                    <td><a href="{{ asset('uploads/' . $item->icon) }}" target="_blank"
                                            class="btn btn-primary btn-sm round">مشاهده</a></td>
                                    <td>{{ jdate($item->created_at)->format('Y/m/d') }}
                                    </td>
                                    <td>{{ jdate($item->created_at)->format('H:m:s') }}
                                    </td>
                                    <td>
                                        <x-button data-bs-toggle="modal" data-bs-target="#edit-sub-category-modal-{{ $item->id }}">ویرایش</x-button>
                                        <x-button color="danger" wire:confirm="آیا از حذف این زیر دسته اطمینان دارید؟" wire:click="deleteSubCategory({{ $item->id }})">حذف</x-button>
                                        <livewire:videos.edit-sub-category :subCategory="$item" :key="'edit-sub-category-'.$item->id" />
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @else
                        <x-alert type="warning" :message="'زیر دسته ای ثبت نشده است!'"/>
                    @endif
                </div>
            @endforeach
        </div>
        {{ $categories->links() }}
    @else
        <x-alert type="warning" :message="'دسته بندی ویدئویی ثبت نشده است!'"/>
    @endif

</div>

@script
    <script>
        let description = CKEDITOR.replace('description');
        let saveBtn = document.getElementById('save-btn');

        CKEDITOR.editorConfig = function( config ) {
            config.language = 'fa';
            config.uiColor = '#F7B42C';
            config.height = 300;
            config.toolbarCanCollapse = true;
        };

        saveBtn.addEventListener('click', function() {
            $wire.set('description', description.getData());
            $wire.call('save');
        });
    </script>
@endscript
