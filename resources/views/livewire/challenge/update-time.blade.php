<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-modals.modal id="update-time-{{$time->id}}" title="ویرایش رمان ">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <x-forms.group for="{{ $time->id }}" label="{{ $key }} ">
                <x-forms.input wire:model="value"/>
                @error('value')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </x-forms.group>
        </div>

        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-success wire:click="update">ثبت</x-buttons.btn-success>
        </x-slot:footer>
    </x-modals.modal>
</div>



