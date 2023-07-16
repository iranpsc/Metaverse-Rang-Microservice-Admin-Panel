<div>
    <div class="row">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
            <div class="input-group mb-3" wire:ignore>
                <select class="form-control round" id="languages" wire:model="selectedLanguage">
                    <option value="">انتخاب زبان</option>
                    @foreach ($languages as $key => $language)
                        <option value="{{ $key }}">{{ $language['name'] }}({{ $language['nativeName'] }})</option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" wire:click="saveTranslation" style="border-radius: 5px 0 0 5px">اضافه کردن ترجمه</button>
                </div>
            </div>
            @error('selectedLanguage')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @if ($translations->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>آیکون</th>
                <th>زبان</th>
                <th>پیشرفت</th>
                <th>تعداد</th>
                <th>انجام شده</th>
                <th>اقدام</th>
            </x-slot:headers>
            @forelse ($translations as $translation)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><img src="https://www.countryflagicons.com/FLAT/64/{{ Str::upper($translation->code) }}.png"></td>
                    <td>{{ $translation->name }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="checkbox" @checked($translation->status) id="languageStatus-{{ $translation->id }}">
                        <a href="{{ route('modals', $translation->id) }}" class="btn btn-primary rounded">
                            <span class="fa fa-edit"></span>
                        </a>
                        <x-buttons.btn-danger id="deleteTranslation-{{ $translation->id }}">
                            <span class="close">&times;</span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $translations->links() }}
    @else
        <x-alerts.danger>ترجمه ای تعریف نشده است</x-alerts.danger>
    @endif


    <script>
        window.addEventListener('livewire:load', function() {
            $(document).ready(function() {
                $('#languages').select2();

                $('#languages').on('select2:select', function (e) {
                    var data = e.params.data;
                    @this.set('selectedLanguage', data.id);
                });

                let deleteTranslation = document.querySelectorAll("[id^='deleteTranslation-']");

                deleteTranslation.forEach(function(element) {
                    element.addEventListener('click', function() {
                        let translationId = element.id.split('-')[1];
                        Swal.fire({
                            title: 'آیا از حذف این ترجمه مطمئن هستید؟',
                            text: "این عمل غیر قابل بازگشت است!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'بله، حذف کن!',
                            cancelButtonText: 'لغو'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                @this.call('deleteTranslation', translationId);
                            }
                        });
                    });
                });

                let languageStatus = document.querySelectorAll("[id^='languageStatus-']");

                languageStatus.forEach(function(element) {
                    element.addEventListener('change', function() {
                        let translationId = element.id.split('-')[1];
                        @this.call('toggleTranslationStatus', translationId);
                    });
                });
            });
        });
    </script>
</div>
