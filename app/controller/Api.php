<?php
namespace app\controller;
use app\service\ServiceOrder;
use think\Exception;

class Api extends Base
{
    public function create_order(){
        $d = input();
        return ServiceOrder::createPayment($d);
//        try {
//            $d = input();
//            return ServiceOrder::createPayment($d);
//        }
//        catch (Exception $ee){
//            dd($ee);
//        }

    }

    public function callback(){
        $d = input();
        ServiceOrder::callBack($d);
    }

}
