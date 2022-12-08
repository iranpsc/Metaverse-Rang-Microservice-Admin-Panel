<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <x-modals.modal id="create-question-answers-modal-{{$question->id}}" title="ویرایش پاسخ های سوال">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <div class="row">
            <div class="col-sm-12">
                <x-forms.group for="answer" label="متن پاسخ">
                    <textarea  class="form-control" wire:model="answer" id="answer" cols="30" rows="10"></textarea>
                    @error('answer')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </x-forms.group>
            </div>
            @if(empty($question->correctAnswer))
                <div class="col-sm-9">
                    <x-forms.group for="correctAnswer" label="علامت به عنوان پاسخ صحیح">
                        <select wire:model="isCorrectAnswer" class="form-control" id="correctAnswer">
                            <option value="">انتخاب کنید</option>
                            <option value="no">خیر</option>
                            <option value="yes">بله</option>
                        </select>
                        @error('correctAnswer')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </x-forms.group>
                </div>
            @endif
        </div>
            <x-slot:footer>
                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                <x-buttons.btn-success wire:click="save({{ $question }})">ثبت</x-buttons.btn-success>
            </x-slot:footer>
    </x-modals.modal>
</div>
