<?php

namespace app\service;

use app\model\Appkey;

class ServiceAppkey extends Base
{

    public function createApp($d=[]){
        $d = glwb($d);
        if(empty($d['uid'])){
            return r_fail('Uid cannot be empty!');
        }
        if(empty($d['app_key'])){
            return r_fail('Please enter app_key!');
        }
        if(empty($d['app_secret'])){
            return r_fail('Please enter app_secret!');
        }
        if(empty($d['sign_key'])){
            return r_fail('Please enter sign_key!');
        }
        if(empty($d['shopify_api_key'])){
            return r_fail('Please enter shopify_api_key!');
        }
        if(empty($d['shopify_api_secret'])){
            return r_fail('Please enter shopify_api_secret!');
        }
        if(empty($d['shopify_access_token'])){
            return r_fail('Please enter shopify_access_token!');
        }
        if(empty($d['shopify_domain'])){
            return r_fail('Please enter shopify_domain!');
        }
        if(empty($d['shopify_shop_name'])){
            return r_fail('Please enter shopify_shop_name!');
        }

        if(!is_numeric($d['uid'])){
            return r_fail('uid error!');
        }

        $app = Appkey::where('app_key',$d['app_key'])->findOrEmpty()->toArray();
        if(!empty($app)){
            return r_fail('app has been created!');
        }
        $allowField = ['uid','app_key','app_secret','sign_key','shopify_api_key','shopify_api_secret','shopify_access_token','shopify_domain','shopify_shop_name'];
        $app = Appkey::create($d,$allowField)->toArray();
        if(empty($app)){
            return r_fail('Failed to create app!');
        }

        return r_ok('ok');
    }

    public function editApp($d=[]){
        $d = glwb($d);
        if(empty($d['id'])){
            return r_fail('ID cannot be empty!');
        }
        if(empty($d['app_key'])){
            return r_fail('Please enter app_key!');
        }
        if(empty($d['app_secret'])){
            return r_fail('Please enter app_secret!');
        }
        if(empty($d['sign_key'])){
            return r_fail('Please enter sign_key!');
        }
        if(empty($d['shopify_api_key'])){
            return r_fail('Please enter shopify_api_key!');
        }
        if(empty($d['shopify_api_secret'])){
            return r_fail('Please enter shopify_api_secret!');
        }
        if(empty($d['shopify_access_token'])){
            return r_fail('Please enter shopify_access_token!');
        }
        if(empty($d['shopify_domain'])){
            return r_fail('Please enter shopify_domain!');
        }
        if(empty($d['shopify_shop_name'])){
            return r_fail('Please enter shopify_shop_name!');
        }
        if(!is_numeric($d['id'])){
            return r_fail('id error!');
        }
        if(!is_numeric($d['uid'])){
            return r_fail('uid error!');
        }

        $app = Appkey::where('app_key',$d['app_key'])->findOrEmpty()->toArray();
        if(!empty($app) && $app['id'] != $d['id']){
            return r_fail('app has been created!');
        }

        $app = Appkey::where('uid',$d['uid'])->where('id',$d['id'])->findOrEmpty()->toArray();
        if(empty($app)){
            return r_fail('app does not exist!');
        }

        $allowField = ['app_key','app_secret','sign_key','shopify_api_key','shopify_api_secret','shopify_access_token','shopify_domain','shopify_shop_name'];
        $app = Appkey::update($d,['id'=>$d['id'],'uid'=>$d['uid']],$allowField)->toArray();
        if(empty($app)){
            return r_fail('Failed to update app!');
        }

        return r_ok('ok');
    }

    public function delApp($d=[]){
        $d = glwb($d);
        if(empty($d['id'])){
            return r_fail('ID cannot be empty!');
        }
        if(!is_numeric($d['id'])){
            return r_fail('id error!');
        }
        if(!is_numeric($d['uid'])){
            return r_fail('uid error!');
        }

        $app = Appkey::where('uid',$d['uid'])->where('id',$d['id'])->findOrEmpty()->toArray();
        if(empty($app)){
            return r_fail('app does not exist.');
        }

        $app = Appkey::where('uid',$d['uid'])->where('id',$d['id'])->delete();
        if(!$app){
            return r_fail('Failed to delete app!');
        }

        return r_ok('ok');
    }

    public function appList($d=[]){
        $d = glwb($d);
        if(empty($d['uid'])){
            return r_fail('Uid cannot be empty!');
        }
        if(!is_numeric($d['uid'])){
            return r_fail('uid error!');
        }

        $app = Appkey::where('uid',$d['uid'])->select()->toArray();
        return r_ok('ok',$app);
    }


}