<div>
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <div class="col-md-12">
        <div class="row">
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="cyan" :value="$users['all']">اعضای ثبت نام کرده</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="blue" :value="$users['verified']">اعضای تایید شده</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="orange" :value="$users['verified-phone']">اعضای احراز شده مرحل اول</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="red" :value="$users['kyc-verified']">اعضای احراز شده مرحله ۲</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="cyan" :value="$dynasties">سلسله های تاسیس شده</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="blue" :value="$features['all']">کل املاک</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="blue" :value="$features['sold']">کل املاک فروخته شده</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="orange" :value="$referrals">کل ورودی با رفرال</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
        </div>
        <div class="row">
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="red" :value="$referral_amount">کل پاداش های دریافتی</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
            <div class="col-lg-3 col-6">
                <x-dashboard.card type="cyan" :value="$deposited_rial_amount">مقدار ریال وارد شده</x-dashboard.card>
            </div><!-- /.col-lg-3 -->
        </div>
    </div>
</div>
