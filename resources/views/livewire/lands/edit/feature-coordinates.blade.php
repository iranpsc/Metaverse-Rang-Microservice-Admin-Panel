<div>
    <x-modal id="modal-{{ $feature->id }}" title="ویرایش مختصات ملک" size="modal-lg modal-dialog-scrollable">
        @foreach ($coordinates as $key => $coordinate)
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group row">
                        <label for="x-{{ $db_coordinates[$key]->id }}" class="form-col-label col-sm-4">X</label>
                        <div class="col-sm-8">
                            <input type="text" id="x-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.x" class="form-control rounded">
                            @error('coordinates.'.$key.'.x')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group row">
                        <label for="y-{{ $db_coordinates[$key]->id }}" class="form-col-label col-sm-4">Y</label>
                        <div class="col-sm-8">
                            <input type="text" id="y-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.y" class="form-control rounded">
                            @error('coordinates.'.$key.'.y')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <x-form.verification/>

        <x-slot:footer>
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot:footer>
    </x-modals.modal>
</div>
