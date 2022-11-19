<div>
    {{-- Do your work, then step back. --}}

    @if ($events->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>متن</th>
                <th>رنگ</th>
                <th>تاریخ شروع</th>
                <th>تاریخ پایان</th>
                <th>ساعت شروع</th>
                <th>ساعت پایان</th>
                <th>تصویر</th>
                <th>تعداد بازدید</th>
                <th>لایک</th>
                <th>دیسلایک</th>
                <th>اقدامات</th>

                @foreach ($events as $event)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $event->title }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($event->content, 20) }}</td>
                        <td>{{ $event->color }}</td>
                        <td>{{ $event->start_date }}</td>
                        <td>{{ $event->end_date }}</td>
                        <td>{{ $event->start_time }}</td>
                        <td>{{ $event->end_time }}</td>
                        <td><a target="_blank" class="btn btn-info btn-sm round" href="{{ $event->image->url }}">مشاهد</a></td>
                        <td>{{ $event->views }}</td>
                        <td>{{$event->likes->count()}}</td>
                        <td>{{$event->dislikes->count()}}</td>                        <td>
                            <x-buttons.btn-danger class="confirm" id="{{ $event->id }}" title="deleteEvent">حذف
                            </x-buttons.btn-danger>
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-tables.table>
    @else
        <x-alerts.danger>وقعه ای ثبت نشده است.</x-alerts.danger>
    @endif

</div>
