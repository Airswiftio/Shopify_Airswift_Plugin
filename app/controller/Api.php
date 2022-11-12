<?php
namespace app\controller;
use app\service\ServiceOrder;
use think\Exception;

class Api extends Base
{
    public function index(){
        return md5('0x08830907F2e2D20A5CB37eE9E0A8BDf8c06e3508');
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


}
