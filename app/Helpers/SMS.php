<?php

namespace App\Helpers;

use Kavenegar;

class SMS {
    public static function send($to, $content) {
        try {
            $receptor = $to;
            $token = $content;
            $token2 = "456";
            $token3 = "789";
            $template = "verify";

            return Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
        } catch (\Kavenegar\Exceptions\ApiException $e) {
            return $e->errorMessage();
        } catch (\Kavenegar\Exceptions\HttpException $e) {
            return $e->errorMessage();
        }

    }
}
