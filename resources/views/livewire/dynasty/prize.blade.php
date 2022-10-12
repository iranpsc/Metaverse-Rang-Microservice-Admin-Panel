<div>
    <button class="btn btn-primary round my-2" data-bs-toggle="modal" data-bs-target="#create-prize">تعریف جوایز</button>
    <x-create-dynasty-prize></x-create-dynasty-prize>

    @if ($prizes->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped text-center" id="data-table">
                <thead>
                    <tr>
                        <th><i class="icon-energy"></i></th>
                        <th>نسبت خانوادگی</th>
                        <th>جزپیات</th>
                        <th>مدیریت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prizes as $prize)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \App\Helpers\getRelationTitle($prize->member) }}</td>
                            <td>
                                <button class="btn btn-primary round" data-bs-toggle="modal"
                                    data-bs-target="#view-prize-{{ $prize->id }}">مشاهده</button>
                            </td>
                            <td>
                                <button class="btn btn-info round" data-bs-toggle="modal"
                                    data-bs-target="#edit-prize-{{ $prize->id }}">ویرایش</button>
                                    <button class="btn btn-danger round"
                                    wire:click="delete({{ $prize->id }})">حذف</button>
                                </td>
                            </tr>
                            @livewire('dynasty.edit-prize', ['prize' => $prize], key($prize->relationship.$prize->id))
                        <x-dynasty-prize-listing :prize="$prize"></x-dynasty-prize-listing>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-alert :type="'danger'" :message="'پاداشی تعریف نشده است'"></x-alert>
    @endif
</div>

@push('js')
    <script>
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>
@endpush
