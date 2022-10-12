<div>
    @if (session()->has('success'))
        <x-alerts.success>{{ session('success') }}</x-alert>
    @endif
    @if ($price_limits)
        <x-tables.table>
            <x-slot:headers>
                <th>تاریخ تغییر</th>
                <th>ساعت تغییر</th>
                <th>نام تغییر دهنده</th>
                <th>میزان تغییر</th>
            </x-slot:headers>
            <tr>
                <td>1</td>
                <td>{{ \Morilog\Jalali\Jalalian::forge($price_limits->updated_at)->format('Y/m/d') }}</td>
                <td>{{ \Morilog\Jalali\Jalalian::forge($price_limits->updated_at)->format('H:m:s') }}</td>
                <td>{{ Auth::user()->name }}</td>
                <td>0</td>
            </tr>
        </x-tables.table>
    @else
        <x-alerts.danger>محدودیت قیمت گذاری تعیین نشده است</x-alerts.danger>
    @endif
    <div class="row my-2">
        <div class="col-sm-6">
            <div class="form-group row">
                <label for="" class="form-col-label col-sm-4">محدودیت قیمت گذاری عموم</label>
                <div class="col-sm-8">
                    <input type="text" wire:model.defer="public_price_limit" class="form-control rounded">
                    @error('public_price_limit')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

        </div>
        <div class="col-sm-6">
            <div class="form-group row">
                <label for="" class="form-col-label col-sm-4">محدودیت قیمت گذاری زیر ۱۸ سال</label>
                <div class="col-sm-8">
                    <input type="text" wire:model.defer="under_eighteen_price_limit" class="form-control rounded">
                    @error('under_eighteen_price_limit')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

        </div>
    </div>

    <button class="btn btn-primary w-25 rounded" wire:click="save">ثبت</button>
</div>
