<?php

namespace Database\Seeders;

use App\Models\StatisticesTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatisticesTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatisticesTypes::truncate();
        $arraytitle = [
            'دنبال کنندگان',
            'حضور در دنیای موازی',
            'خرید رنگ آبی',
            'خرید رنگ زرد',
            'خرید رنگ قرمز',
            'خرید ارز ریال',
            'خرید PSC',
            'خرید ملک مسکونی',
            'خرید ملک تجاری',
            'خرید ملک آموزشی',
            'معامله داخلی',
            'پاداش از سلسله',
            'پاداش از معرفی',
            'معرفی دوستان',
            'سطح اتحاد',
            'اعضای اتحاد',
            'رتبه شهروندی',
            'رتبه خبرنگاری',
            'رتبه مشارکت کننده',
            'رتبه توسعه دهنده',
            'رتبه تجارت',
            'رتبه وکالت',
            'رتبه شورای شهر',
            'رتبه شهردار',
            'رتبه فرماندار',
            'رتبه وزیر',
            'رتبه قاضی',
            'رتبه قانون گذار',
            'سود ساعت شمار رنگ قرمز',
            'سود ساعت شمار رنگ زرد',
            'سود ساعت شمار رنگ آبی',
            'کلید های کسب شده',
            'ساخت بنا',
            'کسب درآمد فیزیکی',
            'کار نیک',
            'ورودی به ملک',
            'درآمد از ورودی',
            'پاسخ صحیح به سوالات',
            'سرمایه گذاری علمی',
            // 'تاریخ',
            // 'ساعت',
            // 'شناسه شهروندی',
            // 'نام شهروند',
            // 'فامیلی شهروند',
            // 'تاریخ تولد',
            // 'نام استان',
            // 'نام شهر',
            // 'سطح شهروند',
            // 'اتحاد',
            // 'امتیاز سطوح',
            // 'حد تاثیر',
            // 'دخیره سرمایه',
            // 'ذخیره دیتا',
            // 'رضایت',
            // 'تحصیلات',
            // 'شماره تماس',
            // 'کشور مورد علاقه',
            // 'زبان مورد علاقه',
            // 'شهر مورد علاقه',
            // 'تخلفات',
        ];



        $arraykey = [
            'Followers',
            'Being_in_a_parallel_world',
            'Buy_blue',
            'Buy_yellow',
            'Buy_red',
            'Buy_Rial_currency',
            'Buy_PSC',
            'Buying_a_residential_property',
            'Purchase_of_commercial_property',
            'Purchase_of_educational_property',
            'Internal_transaction',
            'Reward_from_the_dynasty',
            'Referral_bonus',
            'Introducing_friends',
            'union_level',
            'Alliance_members',
            'Citizenship_rank',
            'Journalist_rank',
            'Contributor_rank',
            'Developer_rating',
            'Trade_rank',
            'Attorney_rank',
            'The_rank_of_the_city_council',
            'The_rank_of_mayor',
            'The_rank_of_governor',
            'Minister_rank',
            'Judge_rank',
            'Legislator_rank',
            'benefit_of_the_red_color_clock',
            'Benefit_of_yellow_hour_counter',
            'benefit_of_the_hour_counter_blue_color',
            'Acquired_keys',
            'Construction_of_the_building',
            'Earn_physical_income',
            'good_job',
            'Entrance_to_the_property',
            'Income_from_input',
            'Correct_answers_to_the_questions',
            'Scientific_investment',
            // 'Date',
            // 'watch',
            // 'Citizenship_ID',
            // 'Name_of_the_citizen',
            // 'Citizens_family',
            // 'Date_of_birth',
            // 'province_name',
            // 'City_Name',
            // 'Citizen_level',
            // 'unity',
            // 'Score_levels',
            // 'limit_of_influence',
            // 'Capital_reserve',
            // 'data_storage',
            // 'Satisfaction',
            // 'education',
            // 'Phone_number',
            // 'Favorite_country',
            // 'Favorite_language',
            // 'Favorite_city',
            // 'Infractions'
        ];

        for ($i = 0; $i < count($arraykey); $i++) {
            StatisticesTypes::create([
                'key' => $arraykey[$i],
                'value' => $arraytitle[$i],
            ]);
        }
    }
}
