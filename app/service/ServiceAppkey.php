<?php

namespace app\service;

class ServiceAppkey extends Base
{

    public function createPayment($d=[]){

        if ($php_result->code !== 200) {
            $msg = "AirSwiftPay's createPayment failed!(".$php_result->message.")";
            self::xielog("$order_id-----$msg");
            return r_fail('Something went wrong, please contact the merchant!');
        } else {
            return r_ok('ok', $php_result->data);
        }
    }


}