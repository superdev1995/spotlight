<?php

use \Carbon\Carbon;


class StringHandler {
    public static function convertBirthdayToMonthCount($birthday) {
        /**
         * Calculate the number of months since the date of birth to correctly
         * fetch the relevant checklist.
         */
        $dob = Carbon::parse($birthday);

        return $dob->diffInMonths(Carbon::now());
    }
    public static function hosturl(){
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'] ;
    }
    public static function validateEmail($string) {
        return filter_var($string, FILTER_VALIDATE_EMAIL);
    }

    public static function getToken($length = 16) {
        $length = round($length / 2);

        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    public static function generateGravatarUrl($email) {
        $hashedEmail = md5(strtolower($email));

        $str = 'https://www.gravatar.com/avatar/'.$hashedEmail.'?d=identicon';

        return $str;
    }
}
