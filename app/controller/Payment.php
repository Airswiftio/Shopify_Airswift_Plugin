<?php
namespace app\controller;

use think\facade\Cache;

class Payment extends Base
{

    public function index()
    {
        $d = input();
        return view('', Cache::get($d['key']??'')??[]);
    }

}
