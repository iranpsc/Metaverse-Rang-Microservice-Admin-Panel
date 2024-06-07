<div>
    <x-modal id="edit-package-modal-{{ $option->id }}" title="بروزرسانی بسته">
        <div class="form-group row">
            <label for="asset" class="col-sm-4 col-form-label">ارز</label>
            <div class="col-sm-8">
                <select class="form-control rounded" id="asset" wire:model="asset">
                    <option selected>ارز را انتخاب کنید</option>
                    @forelse ($variables as $variable)
                        <option value="{{ $variable->asset }}">{{ $variable->getAssetTitle() }}</option>
                    @empty
                        <option disabled>ارزی تعریف نشده است</option>
                    @endforelse
                </select>
                @error('asset')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-form.input label="تعداد" name="amount" />

        <x-form.input label="کد بسته" name="code" />

        <x-form.input type="file" label="تصویر" name="image" />

        <div class="form-group">
            <label for="note">یادداشت</label>
            <textarea wire:model="note" cols="30" rows="10" class="form-control rounded"></textarea>
            @error('note')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>

        @production
            <x-form.verification/>
        @endproduction

        <x-slot:footer>
            <x-button wire:loading.attr="disabled" wire:click="update">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
