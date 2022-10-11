<?php
namespace app\controller;

use app\service\ServiceAppkey;
use app\service\ServiceUser;

class Index extends Base
{

    public function logIn()
    {
        $d = input();
        return ServiceUser::logIn($d);
    }

    public function logOut()
    {

    }

    public function signUp()
    {
        $d = input();
        return ServiceUser::signUp($d);
    }

    public function createApp()
    {
        $d = input();
        $d['uid'] = uid();
        return ServiceAppkey::createApp($d);
    }

    public function appList()
    {
        $d = input();
        $d['uid'] = uid();
        return ServiceAppkey::appList($d);
    }

    public function editApp()
    {
        $d = input();
        $d['uid'] = uid();
        return ServiceAppkey::editApp($d);
    }

    public function delApp()
    {
        $d = input();
        $d['uid'] = uid();
        return ServiceAppkey::delApp($d);
    }

}
