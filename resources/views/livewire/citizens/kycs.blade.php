<div>
    <div class="row">
        <div class="col-sm-6">
            <input type="text" class="form-control rounded w-50 my-3" wire:model.live="search" placeholder="کد ملی را وارد کنید" />
        </div>
        <div class="col-sm-6 d-flex justify-content-end">
            <livewire:citizens.kyc-video-text :key="'kyc-video-text'" />
        </div>
    </div>

    @if ($kycs->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>کد ملی</th>
                <th>مشاهده جزئیات</th>
                <th>وضعیت</th>
            </x-slot:headers>
            @foreach ($kycs as $kyc)
                <tr wire:key="{{ $kyc->id }}">
                    <td>{{ $kyc->id }}</td>
                    <td>{{ $kyc->fname }}</td>
                    <td>{{ $kyc->lname }}</td>
                    <td>{{ $kyc->melli_code }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#modal-kyc-{{ $kyc->id }}">مشاهده</x-button>
                        <livewire:citizens.kyc-details :$kyc :key="'kyc-' . $kyc->id" />
                    </td>
                    <td>{!! $kyc->status_badge !!}</td>
                </tr>
            @endforeach
        </x-table>
        {{ $kycs->links() }}
    @else
        <x-alert type="warning" message="اطلاعاتی تعریف نشده است" />
    @endif
</div>
