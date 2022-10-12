<?php

namespace App\Helpers;

class Feature {
    public static function getKarbari($type) {
        switch($type) {
            case 't':
                return 'تجاری';
                break;
            case 'a':
                return 'آموزشی';
                break;
            case 's':
                return 'فضای سبز';
                break;
            case 'm':
                return 'مسکونی';
                break;

            case 'b':
                return 'بهداشتی';
                break;
            case 'e':
                return 'اداری';
                break;
            case 'f':
                return 'فرهنگی';
                break;
            case 'g':
                return 'گردشگری';
                break;
            case 'z':
                return 'مذهبی';
                break;
            case 'n':
                return 'نمایشگاه';
                break;
        }
    }
}
