<?php
namespace app\controller;

use app\service\ServiceAppkey;
use app\service\ServiceUser;

class Index extends Base
{

    public function logIn()
    {
        $d = input();
        return (new ServiceUser())->logIn($d);
    }

    public function logOut()
    {

    }

    public function signUp()
    {
        $d = input();
        return (new ServiceUser())->signUp($d);
    }

    public function getUserInfo()
    {
        return (new ServiceUser())->getUserInfo(uid());
    }

    public function createApp()
    {
        $d = input();
        $d['uid'] = uid();
        return (new ServiceAppkey())->createApp($d);
    }


    public function getApp()
    {
        $d = input();
        $d['uid'] = uid();
        return (new ServiceAppkey())->getApp($d);
    }

    public function appList()
    {
        $d = input();
        $d['uid'] = uid();
        return (new ServiceAppkey())->appList($d);
    }

    public function editApp()
    {
        $d = input();
        $d['uid'] = uid();
        return (new ServiceAppkey())->editApp($d);
    }

    public function delApp()
    {
        $d = input();
        $d['uid'] = uid();
        return (new ServiceAppkey())->delApp($d);
    }

}
