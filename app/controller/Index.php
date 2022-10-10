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
        $d = input();
        return ServiceUser::logOut($d);
    }

    public function signUp()
    {
        $d = input();
        return ServiceUser::signUp($d);
    }

    public function createApp()
    {
        $d = input();
        return ServiceAppkey::createApp($d);
    }

    public function appList()
    {
        $d = input();
        return ServiceAppkey::appList($d);
    }

    public function editApp()
    {
        $d = input();
        return ServiceAppkey::editApp($d);
    }

    public function delApp()
    {
        $d = input();
        return ServiceAppkey::delApp($d);
    }

}
