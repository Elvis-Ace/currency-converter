<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AfricasTalking\SDK\AfricasTalking;

class SmsController extends Controller
{
    public function sendSms($phone, $message)
    {
        $username = 'elly254'; // use 'sandbox' for development in the test environment
        $apiKey   = 'b099e0193435dbd556ac1425d44a9014b9c08949ae133e883c52b45ef0e9fa4f'; // use your sandbox app API key for development in the test environment
        $AT       = new AfricasTalking($username, $apiKey);

        // Get one of the services
        $sms      = $AT->sms();

        $newphone = $this->validatePhone($phone);
        if ($newphone) {
            $sms->send([
                'to'      => $phone,
                'message' => $message
            ]);
        } else {
            return json_encode(array('error' => "Invalid phone"));
        }
    }
    public function validatePhone($phone)
    {
        if (strpos($phone, '0') === 0 && strlen($phone) == 10) {
            $nphone = "+254" . substr($phone, 1);
        } elseif (strpos($phone, '+') === 0 && strlen($phone) == 13) {
            $nphone = $phone;
        } elseif (strpos($phone, '2') === 0 && strlen($phone) == 12) {
            $nphone = "+" . $phone;
        } else {
            $nphone = false;
        }
        return $nphone;
    }

    public static function mpesaphone($phone){
        if (strpos($phone, '0') === 0 && strlen($phone) == 10) {
            $nphone = "254" . substr($phone, 1);
        } elseif (strpos($phone, '+') === 0 && strlen($phone) == 13) {
            $nphone = $phone;
        } elseif (strpos($phone, '2') === 0 && strlen($phone) == 12) {
            $nphone =$phone;
        } else {
            $nphone = false;
        }
        return $nphone;
    }
}
