<div>

    <x-form.search-box wire:model.live="searchTerm" placeholder="کد ملی را وارد کنید" />

    @if ($kycs->count() > 0)
        <x-table>
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
                <tr wire:key="{{ $kyc->id }}">
                    <td>{{ $kyc->id }}</td>
                    <td>{{ $kyc->fname }}</td>
                    <td>{{ $kyc->lname }}</td>
                    <td>{{ $kyc->melli_code }}</td>
                    <td>{{ $kyc->father_name }}</td>
                    <td>{{ $kyc->user->phone }}</td>
                    <td>{{ $kyc->user->email }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#modal-kyc-{{ $kyc->id }}">مشاهده</x-button>
                        <livewire:citizens.kyc-details :$kyc :key="$kyc->id" />
                    </td>
                    <td>
                        @if ($kyc->status == 0)
                            <x-badge type="warning">در انتظار بررسی</x-badge>
                        @elseif($kyc->status == 1)
                            <x-badge type="success">تایید شده</x-badge>
                        @else
                            <x-badge type="danger">رد شده</x-badge>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $kycs->links() }}
    @else
        <x-alert type="warning" message="اطلاعاتی تعریف نشده است" />
    @endif
</div>
