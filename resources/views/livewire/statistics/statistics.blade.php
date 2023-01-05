<div>
    {{-- Do your work, then step back. --}}

    @if (session()->has('success'))
        <x-alerts.success>{{ session('success') }}</x-alerts.success>
    @endif
    <div class="col-sm-4">
        <select wire:model="statisticType" id="statisticsTypes" class="form-control">
            <option value="" class="disable">انتخاب کنید</option>
            @foreach ($statisticsTypes as $statisticsType)
                <option value="{{ $statisticsType->key }}">{{ $statisticsType->value }}</option>
            @endforeach
        </select>
    </div>
    <div class="conteriner-fluide">
        <div class="row">
            @foreach ($statisticsSettings as $statistic)
                <div class="col-md-3">
                    <div class="input-group">
                        <input class="normal" wire:model="statistic"  value="{{ $statistic->key }}"
                            type="checkbox" id="{{ $statistic->key }}">
                        <label for="{{ $statistic->key }}">{{ $statistic->value }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {{-- <x-tables.table>
        <x-slot name="headers"></x-slot>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Followers" type="checkbox" id="Followers">
                        <label for="Followers">دنبال کنندگان</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Being_in_a_parallel_world" type="checkbox" id="Being_in_a_parallel_world">
                        <label for="Being_in_a_parallel_world">حضور در دنیای موازی</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buy_blue" type="checkbox" id="Buy_blue">
                        <label for="Buy_blue">خرید رنگ آبی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buy_yellow" type="checkbox" id="Buy_yellow">
                        <label for="Buy_yellow">خرید رنگ زرد</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buy_red" type="checkbox" id="Buy_red">
                        <label for="Buy_red">خرید رنگ قرمز</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buy_Rial_currency" type="checkbox" id="Buy_Rial_currency">
                        <label for="Buy_Rial_currency">خرید ارز ریال</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buy_PSC" type="checkbox" id="Buy_PSC">
                        <label for="Buy_PSC">خرید PSC</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Buying_a_residential_property" type="checkbox" id="Buying_a_residential_property">
                        <label for="Buying_a_residential_property">خرید ملک مسکونی</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Purchase_of_commercial_property" type="checkbox" id="Purchase_of_commercial_property">
                        <label for="Purchase_of_commercial_property">خرید ملک تجاری</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Purchase_of_educational_property" type="checkbox" id="Purchase_of_educational_property">
                        <label for="Purchase_of_educational_property">خرید ملک آموزشی</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Internal_transaction" type="checkbox" id="Internal_transaction">
                        <label for="Internal_transaction">معامله داخلی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Reward_from_the_dynasty" type="checkbox" id="Reward_from_the_dynasty">
                        <label for="Reward_from_the_dynasty">پاداش از سلسله</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Referral_bonus" type="checkbox" id="Referral_bonus">
                        <label for="Referral_bonus">پاداش از معرفی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Introducing_friends" type="checkbox" id="Introducing_friends">
                        <label for="Introducing_friends">معرفی دوستان</label>
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="union_level" type="checkbox" id="union_level">
                        <label for="union_level">سطح اتحاد</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Alliance_members" type="checkbox" id="Alliance_members">
                        <label for="Alliance_members">اعضای اتحاد</label>
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Citizenship_rank" type="checkbox" id="Citizenship_rank">
                        <label for="Citizenship_rank">رتبه شهروندی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Journalist_rank" type="checkbox" id="Journalist_rank">
                        <label for="Journalist_rank">رتبه خبرنگاری</label>
                    </div>
                </td>
            </tr>


            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Contributor_rank" type="checkbox" id="Contributor_rank">
                        <label for="Contributor_rank">رتبه مشارکت کننده</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Developer_rating" type="checkbox" id="Developer_rating">
                        <label for="Developer_rating">رتبه توسعه دهنده</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Trade_rank" type="checkbox" id="Trade_rank">
                        <label for="Trade_rank">رتبه تجارت</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Attorney_rank" type="checkbox" id="Attorney_rank">
                        <label for="Attorney_rank">رتبه وکالت</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="The_rank_of_the_city_council" type="checkbox" id="The_rank_of_the_city_council">
                        <label for="The_rank_of_the_city_council">رتبه شورای شهر</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="The_rank_of_mayor" type="checkbox" id="The_rank_of_mayor">
                        <label for="The_rank_of_mayor">رتبه شهردار</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="The_rank_of_governor" type="checkbox" id="The_rank_of_governor">
                        <label for="The_rank_of_governor">رتبه فرماندار</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Minister_rank" type="checkbox" id="Minister_rank">
                        <label for="Minister_rank">رتبه وزیر</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Judge_rank" type="checkbox" id="Judge_rank">
                        <label for="Judge_rank">رتبه قاضی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Legislator_rank" type="checkbox" id="Legislator_rank">
                        <label for="Legislator_rank">رتبه قانون گذار</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="benefit_of_the_red_color_clock" type="checkbox" id="benefit_of_the_red_color_clock">
                        <label for="benefit_of_the_red_color_clock">سود ساعت شمار رنگ قرمز</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Benefit_of_yellow_hour_counter" type="checkbox" id="Benefit_of_yellow_hour_counter">
                        <label for="Benefit_of_yellow_hour_counter">سود ساعت شمار رنگ زرد</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="benefit_of_the_hour_counter_blue_color" type="checkbox" id="benefit_of_the_hour_counter_blue_color">
                        <label for="benefit_of_the_hour_counter_blue_color">سود ساعت شمار رنگ آبی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Acquired_keys" type="checkbox" id="Acquired_keys">
                        <label for="Acquired_keys">کلید های کسب شده</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Construction_of_the_building" type="checkbox" id="Construction_of_the_building">
                        <label for="Construction_of_the_building">ساخت بنا</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Earn_physical_income" type="checkbox" id="Earn_physical_income">
                        <label for="Earn_physical_income">کسب درآمد فیزیکی</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="good_job" type="checkbox" id="good_job">
                        <label for="good_job">کار نیک</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Entrance_to_the_property" type="checkbox" id="Entrance_to_the_property">
                        <label for="Entrance_to_the_property">ورودی به ملک</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Income_from_input" type="checkbox" id="Income_from_input">
                        <label for="Income_from_input">درآمد از ورودی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Correct_answers_to_the_questions" type="checkbox" id="Correct_answers_to_the_questions">
                        <label for="Correct_answers_to_the_questions">پاسخ صحیح به سوالات</label>
                    </div>
                </td>
            </tr>  <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Scientific_investment" type="checkbox" id="Scientific_investment">
                        <label for="Scientific_investment">سرمایه گذاری علمی</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Date" type="checkbox" id="Date">
                        <label for="Date">تاریخ</label>
                    </div>
                </td>
            </tr>
             <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="watch" type="checkbox" id="watch">
                        <label for="watch">ساعت</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Citizenship_ID" type="checkbox" id="Citizenship_ID">
                        <label for="Citizenship_ID">'شناسه شهروندی</label>
                    </div>
                </td>
            </tr>


            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Name_of_the_citizen" type="checkbox" id="Name_of_the_citizen">
                        <label for="Name_of_the_citizen">نام شهروند</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Citizens_family" type="checkbox" id="Citizens_family">
                        <label for="Citizens_family">فامیلی شهروند</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Date_of_birth" type="checkbox" id="Date_of_birth">
                        <label for="Date_of_birth">تاریخ تولد</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="province_name" type="checkbox" id="province_name">
                        <label for="province_name">نام استان</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="City_Name" type="checkbox" id="City_Name">
                        <label for="City_Name">نام شهر</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Citizen_level" type="checkbox" id="Citizen_level">
                        <label for="Citizen_level">سطح شهروند</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="unity" type="checkbox" id="unity">
                        <label for="unity">اتحاد</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Score_levels" type="checkbox" id="Score_levels">
                        <label for="Score_levels">امتیاز سطوح</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="limit_of_influence" type="checkbox" id="limit_of_influence">
                        <label for="limit_of_influence">حد تاثیر</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Capital_reserve" type="checkbox" id="Capital_reserve">
                        <label for="Capital_reserve">دخیره سرمایه</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="data_storage" type="checkbox" id="data_storage">
                        <label for="data_storage">ذخیره دیتا</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Satisfaction" type="checkbox" id="Satisfaction">
                        <label for="Satisfaction">رضایت</label>
                    </div>
                </td>
            </tr> <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="education" type="checkbox" id="education">
                        <label for="education">تحصیلات</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Phone_number" type="checkbox" id="Phone_number">
                        <label for="Phone_number">شماره تماس</label>
                    </div>
                </td>
            </tr>
             <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Favorite_country" type="checkbox" id="Favorite_country">
                        <label for="Favorite_country">کشور مورد علاقه</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Favorite_language" type="checkbox" id="Favorite_language">
                        <label for="Favorite_language">زبان مورد علاقه</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Favorite_city" type="checkbox" id="Favorite_city">
                        <label for="Favorite_city">شهر مورد علاقه</label>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input class="normal" wire:model="Infractions" type="checkbox" id="Infractions">
                        <label for="Infractions">تخلفات</label>
                    </div>
                </td>
            </tr>

    </x-tables.table> --}}
    <button class="btn btn-success mt-3" wire:click="update">ثبت</button>
</div>
