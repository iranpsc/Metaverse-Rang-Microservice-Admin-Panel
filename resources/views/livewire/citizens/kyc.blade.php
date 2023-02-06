<div>
    <x-forms.search-box wire:model="searchTerm" placeholder="کد ملی را وارد کنید" />
    @if (count($kycs) > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>کد ملی</th>
                <th>نام پدر</th>
                <th>شماره موبایل</th>
                <th>ایمیل</th>
                <th>مشاهده جزئیات</th>
                <th>وضعیت</th>
            </x-slot:headers>
            @foreach ($kycs as $kyc)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kyc->fname }}</td>
                    <td>{{ $kyc->lname }}</td>
                    <td>{{ $kyc->melli_code }}</td>
                    <td>{{ $kyc->father_name }}</td>
                    <td>{{ $kyc->user->phone }}</td>
                    <td>{{ $kyc->user->email }}</td>
                    <td>
                        <button type="button" class="btn round btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modal-kyc-{{ $kyc->id }}">مشاهده</button>
                        <livewire:citizens.kyc.details :kyc="$kyc" :wire:key="'kyc-'.$kyc->id">
                    </td>
                    <td>
                        @php
                            echo match($kyc->status) {
                                2 => '<span class="badge badge-warning">اصلاح شده</span>',
                                1 => '<span class="badge badge-success">تایید شده</span>',
                                0 => '<span class="badge badge-warning">بررسی نشده</span>',
                                -1 => '<span class="badge badge-danger">رد شده</span>'
                            };
                        @endphp
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $kycs->links() }}
    @else
        <x-alerts.danger>درخواست احراز هویتی ثبت نشده است</x-alerts.danger>
    @endif
</div>
