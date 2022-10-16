<div>
    <button class="btn btn-primary round my-2" data-bs-toggle="modal" data-bs-target="#create-message">ایجاد پیام</button>
    <x-create-dynasty-message></x-create-dynasty-message>

    @if ($dynastyMessages->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped" id="data-table">
                <thead>
                    <tr>
                        <th><i class="icon-energy"></i></th>
                        <th>نوع پیام</th>
                        <th>متن پیام</th>
                        <th>مدیریت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dynastyMessages as $message)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \App\Helpers\getDynastyMessageTitle($message->type) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($message->message, 10, '...') }}</td>
                            <td>
                                <button class="btn btn-primary round" data-bs-toggle="modal"
                                    data-bs-target="#view-{{ $message->id }}">مشاهده</button>
                                <div id="view-{{ $message->id }}" class="modal fade" wire:ignore.self role="dialog"
                                    tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close"
                                                    data-bs-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">مشاهده پیام</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card-text">{{ $message->message }}</div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-info btn-block round"
                                                    data-bs-dismiss="modal">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-info round" data-bs-toggle="modal"
                                    data-bs-target="#edit-message-{{ $message->id }}">ویرایش</button>
                                    <button class="btn btn-danger round"
                                    wire:click="delete({{ $message->id }})">حذف</button>
                                    @livewire('dynasty.edit-messages', ['message' => $message], key($message->type.$message->id))
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-alerts.danger>پیامی تعریف نشده است</x-alerts.danger>
    @endif
</div>
