<div>
    <div class="row">
        <div class="col-md-6">
            <x-form.input name="psc" label="دریافت PSC" />

            <x-form.input name="blue" label="دریافت رنگ آبی" />

            <x-form.input name="red" label="دریافت رنگ قرمز" />

            <x-form.input name="yellow" label="دریافت رنگ زرد" />
        </div>
        <div class="col-md-6">
            <x-form.input name="satisfaction" label="واحد رضایت" />

            <x-form.input name="effect" label="دریافت حدتاثیر" />
        </div>
    </div>

    <hr>
    <x-form.verification/>
    <hr>
    <x-button class="w-25" wire:click="save">ثبت</x-button>
</div>
