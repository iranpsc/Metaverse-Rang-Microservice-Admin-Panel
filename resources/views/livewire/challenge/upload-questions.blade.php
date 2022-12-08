<div>
    <x-forms.group for="questionsFile" label="درون ریزی">
    <form wire:submit.prevent="upload" enctype="multipart/form-data">
        <input type="file" wire:model="questionsFile">
        <br>
        <button type="submit" class="btn btn-success mt-3">ثبت</button>
        @error('questionsFile')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <p wire:loading wire:target="questionsFile" class="text-success">درحال بارگزاری...</p>
    </form>
    </x-forms.group>
</div>
