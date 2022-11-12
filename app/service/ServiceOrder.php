<?php

namespace app\service;

use think\facade\Cache;
use think\facade\Request;

class ServiceOrder extends Base
{

    private $pay_url_expire_time = 30 * 60;
    private $signKey = 'b412a85ad8b2c5325d280ae78bfb7b5a';
    private $cryptocurrency =['USDC','USDT','ETH'];

    public function preShopify($d=[]){
        $order_id = $d['order_id']??0;
        $app_key = $d['app_key']??'';
        if(empty($order_id)){
            return r_fail('Order Id cannot be empty!');
        }
        if(empty($app_key)){
            return r_fail('Appid Id cannot be empty!');
        }
        $res = (new ServiceShopify())->getOrder($d);
        if($res['code'] !== 1){
            return $res;
        }
        $order_data = $res['data'];
        $total_amount = $order_data['totalPriceSet']['shopMoney']['amount'];

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
        if(strtolower($order_data['currencyCode']) !== 'usd'){
            //all currencies are converted to USD
            $res = currency_conversion($order_data['currencyCode'],$total_amount,$order_id);
            if(isset($res['code']) && $res['code'] === -1){
                return $res;
            }
            $total_amount = $res;
        }

        //pre pay
        $appKey = $appInfo['app_key'];
        $appSecret = $appInfo['app_secret'];
        $tradeType = 0;
        $basicsType = 1;
        $currency_unit = "USDT";
        $nonce = mt_rand(100000,999999);
//                $customer_id = $order->customer_id;
//                $order_note = $order->customer_note;
        $timestamp = floor(microtime(true) * 1000);
        $clientOrderSn = $order_id;
        $sign = md5($appKey.$nonce.$timestamp.$currency_unit.$total_amount.$order_id.$basicsType.$tradeType.$appSecret);
        $data = [
            'appKey' => $appKey,
            'order_id' => $order_id,
            'appSecret' => $appSecret,
            'sign' => $sign,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'clientOrderSn' => $clientOrderSn,
            'tradeType' => $tradeType,
            'coinUnit' =>$currency_unit,
            'basicsType' => $basicsType,
            'amount' => $total_amount,
        ];
        $data['source'] ='shopify';
        $res = $this->pre_pay($data);
        if(!isset($res['code']) || $res['code'] !== 1){
            $this->xielog("$order_id-----{$res['msg']}".json_encode($d));
            return r_fail($res['msg']);
        }
        else{
//            if($res['data']['status'] === 'not_started'){
//            }
            return r_ok('ok',$res['data']['url']);
        }

    }
    public function createPayment($d=[]){
        return r_fail('The key cannot be empty22!');
        dd($d);

        $d['key'] = $d['key']??'';
        $d['cryptocurrency'] = strtoupper($d['cryptocurrency']??'');
        if(empty($d['key'])){
            return r_fail('The key cannot be empty!');
        }
        if(empty($d['cryptocurrency']) || !in_array($d['cryptocurrency'],$this->cryptocurrency)){
            return r_fail('Cryptocurrency error!');
        }
        $data =  Cache::get($d['key']);
        if(empty($data)){
            return r_fail('The order does not exist!');
        }
        $order_id = $data['order_id'];

        //Check whether the appKey and appSecret is exist
        if(empty($data['appKey'])){
            $msg = "AirSwiftPay's appKey is not exist!";
            $this->xielog("$order_id-----$msg".json_encode($d));
            return r_fail('Something went wrong, please contact the merchant for handling1!');
        }
        if(empty($data['appSecret'])){
            $msg = "AirSwiftPay's appSecret is not exist!";
            $this->xielog("$order_id-----$msg".json_encode($d));
            return r_fail('Something went wrong, please contact the merchant for handling2!');
        }

        //Create payment
        $appKey = $data['appKey'];
        $appSecret = $data['appSecret'];
        $tradeType = 0;
        $basicsType = 1;
        $currency_unit = $d['cryptocurrency'];
        $nonce = mt_rand(100000,999999);
        $timestamp = floor(microtime(true) * 1000);
        $total_amount = $data['amount'];
//        $clientOrderSn = $order_id.'_'.mt_rand(100000,999999);
        $clientOrderSn = $order_id;
        $hash_value = md5($appKey.$nonce.$timestamp.$currency_unit.$total_amount.$clientOrderSn.$basicsType.$tradeType.$appSecret);
        $url = "https://order.airswift.io/docking/order/create?appKey=$appKey&sign=$hash_value&timestamp=$timestamp&nonce=$nonce";
        $data1  = [
            'clientOrderSn' => $clientOrderSn,
            'tradeType' => $tradeType,
            'coinUnit' =>$currency_unit,
            'basicsType' => $basicsType,
            'amount' => $total_amount,
            'remarks' =>$appKey,
        ];
        $d = [
            'do'=>'POST',
            'url'=>$url,
            'data'=>json_encode($data1),
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
            $payQrUrl_key = $data['source'].'payQrUrl_'.$order_id;
            Cache::set($payQrUrl_key,$php_result['data'],30 * 60);
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

    public function aa(){

        //Check whether the appKey and appSecret is configured
        if(empty($this->appKey)){
            $msg = "AirSwiftPay's appKey is not set!";
            $order->add_order_note($msg);
            return ['result'=>'success', 'messages'=>home_notice('Something went wrong, please contact the merchant for handling!')];
        }
        if(empty($this->appSecret)){
            $msg = "AirSwiftPay's appSecret is not set!";
            $order->add_order_note($msg);
            return ['result'=>'success', 'messages'=>home_notice('Something went wrong, please contact the merchant for handling!')];
        }
        $total_amount = $order->get_total();
        $paymentCurrency = strtolower($order->get_currency());
        $d = [
            'do'=>'POST',
            'url'=>"https://shopify.airswift.io/api/currency_to_usd",
            'data'=>[
                'order_id'=>$order_id,
                'currencyCode'=>$paymentCurrency,
                'total_amount'=>$total_amount,
            ]
        ];
        $res = json_decode(chttp($d),true);
        if(!isset($res['code']) || $res['code'] !== 1){
            $order->add_order_note($res['msg']);
            return ['result'=>'success', 'messages'=>home_notice('Something went wrong, please contact the merchant for handling!')];
        }
        $total_amount = $res['data'];

        //pre pay
        $appKey = $this->appKey;
        $tradeType = 0;
        $basicsType = 1;
        $currency_unit = "USDT";
        $nonce = mt_rand(100000,999999);
//                $customer_id = $order->customer_id;
//                $order_note = $order->customer_note;
        $timestamp = floor(microtime(true) * 1000);
        $appSecret = $this->appSecret;
        $clientOrderSn = $order_id;
        $sign = md5($appKey.$nonce.$timestamp.$currency_unit.$total_amount.$order_id.$basicsType.$tradeType.$appSecret);
        $data = [
            'appKey' => $appKey,
            'order_id' => $order_id,
            'appSecret' => $appSecret,
            'sign' => $sign,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'clientOrderSn' => $clientOrderSn,
            'tradeType' => $tradeType,
            'coinUnit' =>$currency_unit,
            'basicsType' => $basicsType,
            'amount' => $total_amount,
//                    'remarks' => $order_note,
        ];

        $d = [
            'do'=>'POST',
            'url'=>"https://shopify.airswift.io/woo/api-pre-pay",
            'data'=>$data
        ];
        $res = json_decode(chttp($d),true);
        if(!isset($res['code']) || $res['code'] !== 1){
            $order->add_order_note($res['msg']);
            return ['result'=>'success', 'messages'=>home_notice('Something went wrong, please contact the merchant for handling3!')];
        }
        else{
            if($res['data']['status'] === 'not_started'){
                $order->update_status('processing',  __( 'Awaiting AirSwift Payment', 'airswift-pay-woo'));
            }
//                    $order->reduce_order_stock();
//                    WC()->cart->empty_cart();
            return array(
                'result'   => 'success',
                'redirect' => $res['data']['url'],
                // Redirects to the order confirmation page:
                // 'redirect' => $this->get_return_url($order)
            );
        }
    }


    public function pre_pay($d=[]){
        $order_id = $d['order_id'] ?? 0;
        if(empty($order_id)){
            return r_fail('Order Id cannot be empty!');
        }
        if($d['source'] === 'woo'){
            $source = 'woo_';
        }
        elseif($d['source'] === 'shopify'){
            $source = 'shopify_';
        }
        else{
            return r_fail('Source Error!');
        }
        $payQrUrl_key = $source.'payQrUrl_'.$order_id;
        $data =  Cache::get($payQrUrl_key);
        $nowTime = time();
        $expire_time = $this->pay_url_expire_time;
        $data_key = md5('os_'.$payQrUrl_key);
        Cache::set($data_key,$d,24*60*60);
        if(!is_null($data)){
            $payQrUrl = $data['url'];
            if($nowTime - $data['time'] >= $expire_time){
                //url expire
                return r_ok('ok',['url'=>$payQrUrl,'status'=>'timed_out']);
            }
            else{
                return r_ok('ok',['url'=>$payQrUrl,'status'=>'processing']);
            }
        }
        else{
            return r_ok('ok',['url'=>Request::instance()->domain().'/payment?key='.$data_key,'status'=>'not_started']);
        }
    }


    public function currency_converted_to_usd($data){
        $total_amount = $data['total_amount'];
         $order_id = $data['order_id']?? 0;
        $currencyCode = strtolower($data['currencyCode']);
        //Currency exchange rate conversion, all currencies are converted to USD
        if( $currencyCode !== 'usd'){
            //all currencies are converted to USD
            $res = currency_conversion(strtoupper($currencyCode),$total_amount,$order_id);
            if(isset($res['code']) && $res['code'] === -1){
                return $res;
            }
            $total_amount = $res;
        }
       return r_ok('ok',$total_amount);
    }
}