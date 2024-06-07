<div>
    <div class="row justify-content-center">
        <div class="col-xl-4 col-lg-6 col-md-8">
            <form wire:submit="save">
                <x-form.input name="name" label="نام" />

                <x-form.input type="email" name="email" label="ایمیل" />

                <x-form.input type="password" name="new_access_password" label="رمز دسترسی جدید" />

                <x-form.input type="password" name="new_access_password_confirmation" label="تایید رمز دسترسی جدید" />

                <x-form.input type="password" name="password" label="رمز عبور جدید" />

                <x-form.input type="password" name="password_confirmation" label="تایید رمز عبور جدید" />

                <x-form.input type="file" name="image" label="تصویر" />

                @production
                    <x-form.verification />
                @endproduction

                <x-button type="submit" size="block" color="success">ثبت</x-button>
            </form>
        </div>
    </div>
</div>
