<div>

    <x-form.search-box wire:model="search" />

    @if ($reports->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>عنوان</th>
                <th>متن</th>
                <th>URL</th>
                <th>نام کاربر</th>
                <th>شناسه شهروندی</th>
                <th>تاریخ گزارش</th>
                <th>ساعت گزارش</th>
            </x-slot:headers>
            @foreach ($reports as $report)
                <tr wire:key="{{ $report->id }}">
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->title }}</td>
                    <td>
                        <x-button data-bs-toggle="modal" data-bs-target="#view-report-{{ $report->id }}">مشاهده</x-button>
                        <x-modal id="view-report-{{ $report->id }}" title="توضیحات گزارش">
                            <p class="modal-text">{{ $report->content }}</p>
                            <x-slot:footer>
                                <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
                            </x-slot:footer>
                        </x-modals.modal>
                    </td>
                    <td>{{ $report->url }}</td>
                    <td>{{ $report->user->name }}</td>
                    <td>{{ $report->user->code }}</td>
                    <td>{{ jdate($report->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($report->created_at)->format('H:m:s') }}</td>
                </tr>
            @endforeach
        </x-table>
    @else
        <x-alert type="danger" :message="'گزارشی ثبت نشده است!'"/>
    @endif
</div>
