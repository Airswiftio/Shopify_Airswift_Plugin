<?php

namespace app\service;

use think\facade\Cache;

class ServiceOrder extends Base
{

    public function createPayment($d=[]){
        $order_id = $d['order_id'];
        $payQrUrl_key = 'payQrUrl_'.$order_id;
        $payQrUrl =  Cache::get($payQrUrl_key);
        if(!is_null($payQrUrl)){
            //todo 判断一下交易状态，如果是成功或者失败，则要吧链接销毁
            return r_ok('ok',$payQrUrl);
        }

        $res = (new ServiceShopify())->getOrder($d);
        if($res['code'] !== 1){
            return $res;
        }
        $data = $res['data'];
        $total_amount = $data['totalPriceSet']['shopMoney']['amount'];

        /* if(env('APP_MODE','') === 'production'){
             //At present, the AirSwift payment gateway only supports the conversion of USD to cryptocurrencies, so it is necessary to determine
             if(strtolower($data['currencyCode']) !== 'usd'){
                 return r_fail("AirSwift Payment gateway only supports USD!");
             }
             if(strtolower($data['totalPriceSet']['shopMoney']['currencyCode']) !== 'usd'){
                 return r_fail("AirSwift Payment gateway only supports USD!");
             }
         }*/

        //Get appkey collection information
        $appInfo = (new ServiceShopify())->getAppInfo($d['app_key']);
        if(empty($appInfo)){
            return r_fail('app_key error.');
        }

        //Check whether the appKey and appSecret is exist
        if(empty($appInfo['app_key'])){
            $msg = "AirSwiftPay's appKey is not exist!";
            $this->xielog("$order_id-----$msg".json_encode($d));
            return r_fail('Something went wrong, please contact the merchant for handling1!');
        }
        if(empty($appInfo['app_secret'])){
            $msg = "AirSwiftPay's appSecret is not exist!";
            $this->xielog("$order_id-----$msg".json_encode($d));
            return r_fail('Something went wrong, please contact the merchant for handling2!');
        }

        //Currency exchange rate conversion, all currencies are converted to USD
        if(strtolower($data['currencyCode']) !== 'usd'){
            //all currencies are converted to USD
            $res = currency_conversion($data['currencyCode'],$total_amount,$order_id);
            if(isset($res['code']) && $res['code'] === -1){
                return $res;
            }
            $total_amount = $res;
        }

        //Create payment
        $appKey = $appInfo['app_key'];
        $tradeType = 0;
        $basicsType = 1;
        $currency_unit = "USDT";
        $nonce = mt_rand(100000,999999);
        $timestamp = floor(microtime(true) * 1000);
        $appSecret = $appInfo['app_secret'];
//        $clientOrderSn = $order_id.'_'.mt_rand(100000,999999);
        $clientOrderSn = $order_id;
        $hash_value = md5($appKey.$nonce.$timestamp.$currency_unit.$total_amount.$clientOrderSn.$basicsType.$tradeType.$appSecret);
        $url = "https://order.airswift.io/docking/order/create?appKey=$appKey&sign=$hash_value&timestamp=$timestamp&nonce=$nonce";
        $data  = [
                'clientOrderSn' => $clientOrderSn,
                'tradeType' => $tradeType,
                'coinUnit' =>$currency_unit,
                'basicsType' => $basicsType,
                'amount' => $total_amount,
                'remarks' => $appInfo['app_key'],
            ];
        $d = [
            'do'=>'POST',
            'url'=>$url,
            'data'=>json_encode($data),
            'qt'=>[
                'Content-type: application/json;charset=UTF-8'
            ]
        ];
        $php_result = json_decode(chttp($d),true);
        if ($php_result['code'] !== 200) {
            $msg = "AirSwiftPay's createPayment failed!({$php_result['message']})";
            $this->xielog("$order_id-----$msg".json_encode($d));
            return r_fail($php_result['message']);
        } else {
            Cache::set($payQrUrl_key,$php_result['data'],30 * 60 - 2);
            return r_ok('ok', $php_result['data']);
        }
    }

    public function callBack($d = []){

        if(empty($d) || !isset($d['sign']) || !isset($d['clientOrderSn']) || !isset($d['coinUnit']) || !isset($d['amount']) || !isset($d['rate']) ) {
            $this->xielog($d);
            exit('failed');
        }
        else{
            $this->xielog($d);
        }

        //Get appkey collection information
        $appInfo = (new ServiceShopify())->getAppInfo($d['remarks']);
        if(empty($appInfo)){
            return r_fail('app_key error.');
        }

        //Verify signature
        $sign = md5($appInfo['sign_key'].$d['clientOrderSn'].$d['coinUnit'].$d['amount'].$d['rate']);
        if(strtolower($sign) !== strtolower($d['sign'])){
            $d['err_msg'] = 'sign error:'.$sign;
            $this->xielog($d);
            exit('failed');
        }

//        $order_id = explode('_',$d["clientOrderSn"])[0];
        $order_id = $d["clientOrderSn"];
        if ($d["status"] == 1) {
            $res = (new ServiceShopify())->orderMarkPaid(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id]);
            if($res['code'] === 1){
                $this->xielog("$order_id-----completed-----Order has been paid.");
                exit('SUCCESS');
            }
            else{
                $this->xielog("$order_id-----completed-----{$res['msg']}");
            }


        }
        else if ($d["status"] == 2) {
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id,'reason'=>'other']);
            if($res['code'] === 1) {
                $this->xielog("$order_id-----failed-----Order is failed.");
                exit('SUCCESS');
            }
            else{
                $this->xielog("$order_id-----failed-----{$res['msg']}");
            }

        }
        else if ($d["status"] == 3) {
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id,'reason'=>'customer']);
            if($res['code'] === 1) {
                $this->xielog("$order_id-----cancelled-----Order is cancelled.");
                exit('SUCCESS');
            }
            else{
                $this->xielog("$order_id-----cancelled-----{$res['msg']}");
            }
        }

        $this->xielog("$order_id-----AirSwiftPay Payment Status:{$d["status"]}.");
        exit('failed');
    }

}