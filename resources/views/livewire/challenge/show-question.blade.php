<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-modals.modal id="show-question-modal-{{$question->id}}" title="نمایش اطلاعات سوال">
        <div class="row">
            <div class="col-sm-12">
                <x-forms.group for="title" label="عنوان">
                    <span>{{ $title }}</span>
                </x-forms.group>

                <div class="col-sm-12">
                    <x-forms.group for="code" label="کد">
                       <span>{{ $code }}</span>
                    </x-forms.group>
                </div>

                <div class="col-sm-12">
                    <x-forms.group for="code" label="لیست پاسخ های سوال">
                        <span>سسسس</span>
                        <span>سسسس</span>
                        <span>سسسس</span>
                        <span>سسسس</span>
                    </x-forms.group>
                </div>

                <div class="col-sm-12">
                    <x-forms.group for="code" label="بهترین پاسخ">
                        <span>سسس</span>
                    </x-forms.group>
                </div>

            </div>
            <x-slot:footer>
                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            </x-slot:footer>
    </x-modals.modal>
</div>
