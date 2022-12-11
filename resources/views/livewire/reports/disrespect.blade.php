<div>
    {{-- Stop trying to control. --}}
    @if ($reports->count() > 0)
        <x-tables.table>
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
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->title }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#view-report-{{ $report->id }}">مشاهده</x-buttons.btn-primary>
                        <x-modals.modal id="view-report-{{ $report->id }}" title="توضیحات گزارش">
                            <p class="modal-text">{{ $report->content }}</p>
                            <x-slot:footer>
                                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                            </x-slot:footer>
                        </x-modals.modal>
                    </td>
                    <td>{{ $report->url }}</td>
                    <td>{{ $report->user->name }}</td>
                    <td>{{ $report->user->code }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($report->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($report->created_at)->format('H:m:s') }}</td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>گزارشی ثبت نشده است!</x-alerts.danger>
    @endif
</div>
