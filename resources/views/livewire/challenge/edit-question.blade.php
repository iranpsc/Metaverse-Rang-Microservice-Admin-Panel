<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-modals.modal id="edit-question-modal-{{$question->id}}" title="ویرایش اطلاعات سوال">
        @if (session('question-updated'))
            <x-alerts.success>{{ session('question-updated') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-12">
                <x-forms.group for="title" label="عنوان">
                    <x-forms.input id="title" wire:model="title" />
                    @error('title')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>

                <div class="col-sm-12">
                    <x-forms.group for="code" label="کد">
                        <x-forms.input id="code" wire:model="code" />
                        @error('code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </x-forms.group>
            </div>

        </div>
        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-success wire:click="update">ثبت</x-buttons.btn-success>
        </x-slot:footer>
    </x-modals.modal>
</div>
