<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary rounded mt-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
        بارگذاری متن احراز ویدیویی
    </button>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت متن احراز ویدیویی</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control rounded" rows="5" wire:model="text"></textarea>
                    @error('text')
                        <span class="form-text text-danger">{{ $message }}</span>
                    @enderror

                    @production
                        <div class="alert alert-warning rounded mt-3">
                            <strong>توجه!</strong>
                            <p>
                                در صورتی که متن احراز ویدیویی تعریف شده باشد، امکان تغییر آن وجود ندارد.
                            </p>
                        </div>
                        <x-form.verification />
                    @endproduction

                    <hr>
                    @forelse ($texts as $text)
                        <div class="alert alert-info rounded">
                            {{ $text->text }}
                        </div>
                        <button class="btn btn-danger rounded mb-2" wire:click="delete({{ $text->id }})">حذف</button>
                    @empty
                        <x-alert type="warning" message="اطلاعاتی تعریف نشده است" />
                    @endforelse
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger rounded" data-bs-dismiss="modal">بستن</button>
                    <button type="button" class="btn btn-primary rounded" wire:click="save">ثبت</button>
                </div>
            </div>
        </div>
    </div>
</div>
