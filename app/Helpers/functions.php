<?php

namespace App\Helpers;
use Illuminate\Support\Carbon;

use function PHPUnit\Framework\returnSelf;

function getDynastyMessageTitle($type)
{
    switch ($type) {
        case 'requester':
            return 'پیام تایید درخواست کننده';
            break;
        case 'reciever':
            return 'پیام دریافت کننده درخواست';
            break;
        case 'requester_accept':
            return 'پیام تایید پذیرش پیوستن به سلسله';
            break;
        case 'reciever_accept':
            return 'پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست';
            break;
        case 'dynasty_prize':
            return 'پیام پاداش برای درخواست کننده سلسله';
            break;
    }
}

function getRelationTitle($member)
{
    switch ($member) {
        case 'father':
            return 'پدر';
            break;
        case 'mother':
            return 'مادر';
            break;
            case 'life_partner':
                return 'همسر';
                break;
        case 'brother':
            return 'برادر';
            break;
        case 'sister':
            return 'خواهر';
            break;
        case 'offspring':
            return 'فرزند';
            break;
        case 'wife':
            return 'زن';
            break;
        case 'husband':
            return 'شوهر';
            break;
    }
}

function getAssetColor($asset) {
    switch ($asset) {
        case 'red':
            return 'قرمز';
            break;

        case 'irr':
            return 'ریال';
            break;

        case 'psc':
            return 'psc';
            break;

        case 'blue':
            return 'آبی';
            break;

        case 'yellow':
            return 'زرد';
            break;
    }
}

function getTicketPriorityTitle($importance) {
    switch($importance) {
        case 0:
            return 'متوسط';
            break;
        case 1:
            return 'زیاد';
            break;
        case -1:
            return 'کم';
            break;
    }


}

function convertDateToCarbon($date)
{
    $date = \Morilog\Jalali\CalendarUtils::convertNumbers($date, true);
    $date = str_replace('/', '-', $date);
    $date = Carbon::parse($date)->format('Y-m-d');
    $date = \Morilog\Jalali\CalendarUtils::createCarbonFromFormat('Y-m-d', $date)->format('Y-m-d');
    return $date;
}
