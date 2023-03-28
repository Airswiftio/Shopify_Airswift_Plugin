<?php

namespace app\service;

use think\facade\Cache;
use think\facade\Request;

class ServiceOrder extends Base
{

    private $pay_url_expire_time = 30 * 60;
//    private $cryptocurrency =['USDC','USDT','ETH','CNT'];

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
            $this->xielog("$order_id-----$msg",$d);
            return r_fail('Something went wrong, please contact the merchant for handling1!');
        }
        if(empty($appInfo['app_secret'])){
            $msg = "AirSwiftPay's appSecret is not exist!";
            $this->xielog("$order_id-----$msg",$d);
            return r_fail('Something went wrong, please contact the merchant for handling2!');
        }

        //Currency exchange rate conversion, all currencies are converted to USD
        $currencyCode = strtoupper($order_data['currencyCode']);
        if(strtolower($currencyCode) !== 'usd'){
            //all currencies are converted to USD
            $res = currency_conversion($currencyCode,$total_amount,$order_id);
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
            $this->xielog("$order_id-----{$res['msg']}",$d);
            return r_fail($res['msg']);
        }
        else{
//            if($res['data']['status'] === 'not_started'){
//            }
            return r_ok('ok',$res['data']['url']);
        }

    }
    public function createPayment($d=[]){
        $d['key'] = $d['key']??'';
        $d['cryptocurrency'] = strtoupper($d['cryptocurrency']??'');
        if(empty($d['key'])){
            return r_fail('The key cannot be empty!');
        }
        if(empty($d['cryptocurrency'])){
            return r_fail('Cryptocurrency error!');
        }
        $data =  Cache::get($d['key']);
        if(empty($data)){
            return r_fail('The order does not exist!');
        }
        $order_id = $data['order_id'];
        $payQrUrl_key = $data['source'].'_'.$d['cryptocurrency'].'_payQrUrl_'.$data['order_id'];
        $data_url =  Cache::get($payQrUrl_key);
        if(!is_null($data_url)){
            return r_ok('ok',$data_url['url']);
        }

        //Check whether the appKey and appSecret is exist
        if(empty($data['appKey'])){
            $msg = "AirSwiftPay's appKey is not exist!";
            $this->xielog("$order_id-----$msg",$d);
            return r_fail('Something went wrong, please contact the merchant for handling1!');
        }
        if(empty($data['appSecret'])){
            $msg = "AirSwiftPay's appSecret is not exist!";
            $this->xielog("$order_id-----$msg",$d);
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
        $total_amount = ceil($data['amount']*100)/100;
        $clientOrderSn = $order_id.'_'.time();
//        $clientOrderSn = $order_id;
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
            $this->xielog("$order_id-----$msg",$d);
            return r_fail($php_result['message']);
        } else {

            $this->xielog("CreatePayment-----$order_id",$d);
            $payQrUrl_key = $data['source'].'_'.$currency_unit.'_payQrUrl_'.$order_id;
            Cache::set($payQrUrl_key,['url'=>$php_result['data'],'time'=>time()],24*60*60);
            return r_ok('ok', $php_result['data']);
        }
    }

    public function callBack($d = []){
        $this->xielog("callBack1-----{$d['clientOrderSn']}",$d);
        if(empty($d) || !isset($d['sign']) || !isset($d['clientOrderSn']) || !isset($d['coinUnit']) || !isset($d['amount']) || !isset($d['rate']) ) {
            exit('failed');
        }
        $order_id = explode('_',$d['clientOrderSn'])[0];
//        $order_id = $clientOrderSn;

        //Get appkey collection information
        $appInfo = (new ServiceShopify())->getAppInfo($d['remarks']);
        if(empty($appInfo)){
            $d['err_msg'] = $order_id.' app_key error.';
            $this->xielog("callBack2-----{$d['clientOrderSn']}",$d);
            exit('failed');
        }

        //Verify signature
        $sign = md5($appInfo['sign_key'].$d['clientOrderSn'].$d['coinUnit'].$d['amount'].$d['rate']);
        if(strtolower($sign) !== strtolower($d['sign'])){
            $d['err_msg'] = $order_id.' sign error:'.$sign;
            $this->xielog("callBack3-----{$d['clientOrderSn']}",$d);
            exit('failed');
        }

        if ($d["status"] == 1) {
            // payStatus = 0 is pending, 1 is received, 2 is cancel, 3 is not enough payment, 4 is over pay
            // Query the order details. When the payStatus is 1 or 4, the order is marked as paid (completed)
            $orderSn = $d['orderSn'];
            $appKey = $appInfo['app_key'];
            $appSecret = $appInfo['app_secret'];
            $nonce = mt_rand(100000,999999);
            $timestamp = floor(microtime(true) * 1000);
            $sign = md5($appKey.$nonce.$timestamp.$appSecret);
            $url = "https://order.airswift.io/docking/order/detail/{$orderSn}?appKey={$appKey}&sign={$sign}&timestamp={$timestamp}&nonce={$nonce}";
            $d = [
                'do'=>'POST',
                'url'=>$url,
                'data'=>json_encode([]),
                'qt'=>[
                    'Content-type: application/json;charset=UTF-8'
                ]
            ];
            $res1 = json_decode(chttp($d),true);
            $message = [
                'order_id'=>$order_id,
                'status'=>'completed',
                'order'=>$res1['data'],
            ];
            if($res1['data']['payStatus'] == 1 || $res1['data']['payStatus'] == 4){
                $res = (new ServiceShopify())->orderMarkPaid(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id]);
                if($res['code'] === 1){
                    $message['msg'] = 'Order has been paid.';
                    $this->xielog($message);
                    exit('SUCCESS');
                }
                else{
                    $message['msg'] =$res['msg'];
                    $this->xielog($message);
                }
            }
            elseif($res1['data']['payStatus'] ==3){
                $message['msg'] ='not enough payment';
                $this->xielog($message);
                $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id,'reason'=>'other']);
                if($res['code'] === 1){
                    $message['msg'] = 'Order has been paid(not enough payment).';
                    $this->xielog($message);
                    exit('SUCCESS');
                }
                else{
                    $message['msg'] =$res['msg'];
                    $this->xielog($message);
                }
            }
            else{
                $message['msg'] ='unknow error';
                $this->xielog($message);
//                exit('SUCCESS');
            }
        }
        else if ($d["status"] == 2) {
            $message = [
                'order_id'=>$order_id,
                'status'=>'failed',
                'order'=>$d,
            ];
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id,'reason'=>'other']);
            if($res['code'] === 1) {
                $message['msg'] ='Order is failed.';
                $this->xielog($message);
                exit('SUCCESS');
            }
            else{
                $message['msg'] =$res['msg'];
                $this->xielog($message);
            }

        }
        else if ($d["status"] == 3) {
            $message = [
                'order_id'=>$order_id,
                'status'=>'cancelled',
                'order'=>$d,
            ];
            $res = (new ServiceShopify())->orderCancel(['app_key'=>$appInfo["app_key"],'order_id'=>$order_id,'reason'=>'customer']);
            if($res['code'] === 1) {
                $message['msg'] ='Order is cancelled.';
                $this->xielog($message);
                exit('SUCCESS');
            }
            else{
                $message['msg'] =$res['msg'];
                $this->xielog($message);
            }
        }

        $this->xielog("$order_id-----AirSwiftPay Payment Status:{$d["status"]}.");
        exit('failed');
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
        $payQrUrl_key = $source.$d['coinUnit'].'_payQrUrl_'.$order_id;
        $data_key = md5('os_'.$payQrUrl_key);
        $data =  Cache::get($payQrUrl_key);
        $nowTime = time();
        $expire_time = $this->pay_url_expire_time;
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