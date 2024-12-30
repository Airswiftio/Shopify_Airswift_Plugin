<?php

namespace app\service;
use think\facade\Db;
use think\facade\Log;

class Base
{
    public function xielog($msg = '',$nr = []){
        if(is_array($nr) || is_object($nr)){
            $nr = json_encode($nr);
        }
        Db::table('asp_log')->save(['nr'=>$nr,'msg'=>$msg]);
    }
}