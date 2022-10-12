<div>
    <x-forms.search-box wire:model="searchTerm" placeholder="نام یا نام خانوادگی را وارد کنید"></x-forms.search-box>
    @if ($kycs->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>نام بانک</th>
                <th>شماره شبا</th>
                <th>وضعیت</th>
            </x-slot:headers>
            @foreach ($kycs as $kyc)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kyc->lname }}</td>
                    <td>{{ $kyc->fname }}</td>
                    <td>{{ $kyc->bank }}</td>
                    <td>{{ $kyc->shaba }}</td>
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
        <x-alerts.danger>حسباب بانکی ثبت نشده است</x-alerts.danger>
    @endif
</div>
