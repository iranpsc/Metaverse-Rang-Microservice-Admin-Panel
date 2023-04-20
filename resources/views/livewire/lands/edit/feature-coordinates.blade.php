<div>
    <x-modals.modal id="modal-{{ $feature->id }}" title="ویرایش مختصات ملک" size="modal-lg modal-dialog-scrollable">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @foreach ($coordinates as $key => $coordinate)
            <div class="row">
                <div class="col-sm-6">
                    <x-forms.group for="x-{{ $db_coordinates[$key]->id }}" label="X">
                        <x-forms.input id="x-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.x" />
                        @error('coordinates.'.$key.'.x')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </x-forms.group>
                </div>
                <div class="col-sm-6">
                    <x-forms.group for="y-{{ $db_coordinates[$key]->id }}" label="Y">
                        <x-forms.input id="y-{{ $db_coordinates[$key]->id }}" wire:model="coordinates.{{ $key }}.y" />
                        @error('coordinates.'.$key.'.y')
                        <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </x-forms.group>
                </div>
            </div>
        @endforeach

        <x-forms.verification id="{{$feature->id}}" />
        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>
</div>
