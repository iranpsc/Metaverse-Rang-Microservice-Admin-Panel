<div>
    <x-table>
        <x-slot name="headers"></x-slot>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="BFR" type="checkbox" id="permission1">
                        <label for="permission1">قابیلیت خرید از فروشگاه متارنگ</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="PIUP" type="checkbox" id="permission6">
                        <label for="permission6">قابلیت شرکت در پروژه های اتحادی</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="SF" type="checkbox" id="permission2">
                        <label for="permission2">قابلیت فروش املاک و مسغلات در متارنگ</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="PITC" type="checkbox" id="permission7">
                        <label for="permission7">قابلیت شرکت در چالش ها </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="W" type="checkbox" id="permission3">
                        <label for="permission3">خارج کردن سرمایه از متارنگ </label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="PIC" type="checkbox" id="permission8">
                        <label for="permission8">قابلیت شرکت در مسابقات </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="JU" type="checkbox" id="permission4">
                        <label for="permission4">قابلیت ورود به اتحاد ها </label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="ESOO" type="checkbox" id="permission9">
                        <label for="permission9">قابلیت تاسیس فروشگاه یا دفتر کار</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="DM" type="checkbox" id="permission5">
                        <label for="permission5">قابیلت مدیریت سلسله </label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="COTB" type="checkbox" id="permission10">
                        <label for="permission10">قابیلت هم کاری در ساخت بنا</label>
                    </div>
                </td>
            </tr>

    </x-table>
    <x-button color="info" wire:click="update">ثبت</x-button>
</div>
