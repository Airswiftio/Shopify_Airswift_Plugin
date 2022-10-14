<?php
namespace app\controller;
use app\service\ServiceOrder;
use think\Exception;

class Api extends Base
{
    public function index(){
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

}
