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
                        <livewire:citizens.kyc.details :kyc="$kyc" :wire:key="$kyc->id">
                    </td>
                    <td>
                        @php
                            switch ($kyc->status) {
                                case 1:
                                    echo '<span class="badge badge-success">تایید شده</span>';
                                    break;

                                case 0:
                                    echo '<span class="badge badge-warning">درحال پردازش</span>';
                                    break;

                                case -1:
                                    echo '<span class="badge badge-danger">رد شده</span>';
                                    break;
                            }
                        @endphp
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>درخواست احراز هویتی ثبت نشده است</x-alerts.danger>
    @endif
</div>
