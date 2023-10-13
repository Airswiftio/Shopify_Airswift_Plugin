<?php
namespace app\controller;
use app\service\ServiceOrder;

class Api extends Base
{
    public function index(){
//        $d = '{"amount":"0.00000837","address":"0xb59c3df382a884e1c77cf3e17ddef60c2ac1b3c8","orderSn":"648179955885719552","payTime":1668410283135,"clientOrderSn":"4747667636373","fee":0,"sign":"286FE410CD93CC7A8CBFE15C7B0A2FBC","cnyAmount":0.01,"cancelTime":0,"createTime":1668410144000,"rate":"1193.46000000","id":457,"coinUnit":"ETH","remarks":"8e6bd0d6-4ada-4b97-8840-183422700f81","tradeType":0,"status":1}';
//        $d = json_decode($d,true);
//        $order_id = $d["clientOrderSn"];
//
//        dump($d);
//        //Get appkey collection information
//        $appInfo = (new ServiceShopify())->getAppInfo($d['remarks']);
//        if(empty($appInfo)){
//            $d['err_msg'] = $order_id.' app_key error.';
//            $this->xielog($d);
//            exit('failed');
//        }
//
//        //Verify signature
//        $sign = md5($appInfo['sign_key'].$d['clientOrderSn'].$d['coinUnit'].$d['amount'].$d['rate']);
//        if(strtolower($sign) !== strtolower($d['sign'])){
//            $d['err_msg'] = $order_id.' sign error:'.$sign;
//            $this->xielog($d);
//            exit('failed');
//        }
//
//        $orderSn = $d['orderSn'];
//        $appKey = $appInfo['app_key'];
//        $appSecret = $appInfo['app_secret'];
//        $nonce = mt_rand(100000,999999);
//        $timestamp = floor(microtime(true) * 1000);
//        $sign = md5($appKey.$nonce.$timestamp.$appSecret);
//        $url = "https://order.airswift.io/docking/order/detail/{$orderSn}?appKey={$appKey}&sign={$sign}&timestamp={$timestamp}&nonce={$nonce}";
//        $d = [
//            'do'=>'POST',
//            'url'=>$url,
//            'data'=>json_encode([]),
//            'qt'=>[
//                'Content-type: application/json;charset=UTF-8'
//            ]
//        ];
//        $res1 = json_decode(chttp($d),true);
//        dd('ok',$res1);

       return 'Hello!';
    }

    public function create_order(){
        $d = input();
        return (new ServiceOrder())->createPayment($d);
    }

    public function callback(){
        $d = input();
        return (new ServiceOrder())->callBack($d);
    }

    public function woo_pre_pay(){
        $d = input();
        $d['source'] ='woo';
        return (new ServiceOrder())->pre_pay($d);
    }

    public function pre_shopify(){
        $d = input();
        return (new ServiceOrder())->preShopify($d);
    }

    public function currency_to_usd(){
        $d = input();
        return (new ServiceOrder())->currency_converted_to_usd($d);
    }


    public function wlog(){
        $d = input();
        (new \app\service\Base())->xielog("woo--{$d['message1']}",$d);
    }


    public function test(){

        dd('test');
        $orderSn = '766684733506109440';
        $appKey = 'c3e50e98-2dfb-4dca-84cb-314ef3781cfd';
        $nonce = mt_rand(100000,999999);
        $timestamp = floor(microtime(true) * 1000);
        $data = [
            'appKey'=>$appKey,
            'nonce'=>$nonce .'',
            'orderSn'=>$orderSn,
            'timestamp'=>$timestamp .'',
        ];
        ksort($data);
        $data = array_filter($data, "removeEmptyValues");
        $sData = implode('',$data);
        $sign =  encodeSHA256withRSA($sData);
        $url = "https://order.airswift.io/docking/order/detail";
        $bizContent = json_encode($data);
        $post_data =  [
            'signStr'=>$sign,
            'bizContent'=>$bizContent
        ];

        $output = wPost($url,$post_data);
        dd('123225',$output);
    }


}
