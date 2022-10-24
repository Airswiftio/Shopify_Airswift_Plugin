<?php

namespace app\service;

class ServiceOrder extends Base
{

    public function createPayment($d=[]){
        $order_id = $d['order_id'];
        $res = (new ServiceShopify())->getOrder($d);
        if($res['code'] !== 1){
            return $res;
        }
        $data = $res['data'];
        $total_amount = $data['totalPriceSet']['shopMoney']['amount'];

        //Currency exchange rate conversion, all currencies are converted to USD
        if(strtolower($data['currencyCode']) !== 'usd'){
            //all currencies are converted to USD
            // api.5
            $d1 = [
                'do'=>'GET',
//                'url'=>'https://api.apilayer.com/exchangerates_data/convert?to=USD&from=CNY&amount=1',
                'url'=>"https://api.apilayer.com/exchangerates_data/convert?to=USD&from={$data['currencyCode']}&amount={$total_amount}",
                'qt'=>[
                    'apikey: vIc43zNe7qA5yVPpAb560Uo4wXnPhrdA',
                    'Content-Type: text/plain'
                ]
            ];
            $res = json_decode(chttp($d1),true);
            if($res['success'] === true){
                $total_amount = $res['result'];
            }
            else {
                $this->xielog("$order_id-----{$res['message']}");
                return r_fail('Currency exchange rate conversion failed!');
            }
            // api.11
            /*$d = [
                'do'=>'POST',
                'url'=>'https://neutrinoapi.net/convert',
                'data'=>[
                    'from-value'=>$total_amount,
                    'from-type'=>$data['currencyCode'],
                    'to-type'=>"USD",
                ],
                'qt'=>[
                    'user-id: 644577519@qq.com',
                    'api-key: VzLCqZFwsJVqo2BlcICVMcP06u7PmLhsMT5YzlnDSUq3iHTL',
                ]
            ];
            $res = json_decode(chttp($d),true);
            if($res['valid'] === true){
                $total_amount = $res['result'];
            }
            else{
                return r_fail('Currency exchange rate conversion failed!');
            }*/
        }

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
            $this->xielog("$order_id-----$msg");
            return r_fail('Something went wrong, please contact the merchant for handling!');
        }
        if(empty($appInfo['app_secret'])){
            $msg = "AirSwiftPay's appSecret is not exist!";
            $this->xielog("$order_id-----$msg");
            return r_fail('Something went wrong, please contact the merchant for handling!');
        }

        //Create payment
        $order_id = '999'.time();
        $appKey = $appInfo['app_key'];
        $tradeType = 0;
        $basicsType = 1;
        $currency_unit = "USDT";
        $nonce = mt_rand(100000,999999);
        $timestamp = floor(microtime(true) * 1000);
        $appSecret = $appInfo['app_secret'];
        $clientOrderSn = $order_id;
        $hash_value = md5($appKey.$nonce.$timestamp.$currency_unit.$total_amount.$order_id.$basicsType.$tradeType.$appSecret);
        $url = "https://order.airswift.io/docking/order/create?appKey=$appKey&sign=$hash_value&timestamp=$timestamp&nonce=$nonce";
        $data = array(
            'clientOrderSn' => $clientOrderSn,
            'tradeType' => $tradeType,
            'coinUnit' =>$currency_unit,
            'basicsType' => $basicsType,
            'amount' => $total_amount,
            'remarks' => $appInfo['app_key'],
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json;charset=UTF-8",
                'method'  => 'POST',
                'content' => json_encode($data),
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $php_result = json_decode($result);
        if ($php_result->code !== 200) {
            $msg = "AirSwiftPay's createPayment failed!(".$php_result->message.")";
            $this->xielog("$order_id-----$msg");
            return r_fail('Something went wrong, please contact the merchant!');
        } else {
            return r_ok('ok', $php_result->data);
        }
    }

    public function callBack($d = []){

        if(empty($d) || !isset($d['sign']) || !isset($d['clientOrderSn']) || !isset($d['coinUnit']) || !isset($d['amount']) || !isset($d['rate']) ) {
            $this->xielog($d);
            exit('failed');
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

        $order_id = $d["clientOrderSn"];
        if ($d["status"] == 1) {
            $res = (new ServiceShopify())->orderMarkPaid(['app_key'=>$appInfo["app_key"],'order_id'=>$d["clientOrderSn"]]);
            if($res['code'] === 1){
                $this->xielog("$order_id-----completed-----Order has been paid.");
                exit('SUCCESS');
            }
            else{
                $this->xielog("$order_id-----completed-----{$res['msg']}");
            }


        }
        else if ($d["status"] == 2) {
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$d["clientOrderSn"],'reason'=>'other']);
            if($res['code'] === 1) {
                $this->xielog("$order_id-----failed-----Order is failed.");
                exit('SUCCESS');
            }
            else{
                $this->xielog("$order_id-----failed-----{$res['msg']}");
            }

        }
        else if ($d["status"] == 3) {
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$d["clientOrderSn"],'reason'=>'customer']);
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