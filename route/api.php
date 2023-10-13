<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::group( function(){
    Route::post('wlog', 'api/wlog');

    Route::get('/', 'api/index');
    Route::post('/api/currency_to_usd', 'api/currency_to_usd');
    Route::post('/woo/api-pre-pay', 'api/woo_pre_pay');
    Route::post('/pre_shopify', 'api/pre_shopify');
    Route::get('/test', 'api/test');

    Route::group( function(){
        Route::post('api-create_payment', 'api/create_order');
        Route::any('callback', 'api/callback');
    });

    Route::group('app', function(){
        Route::post('logIn', 'index/logIn');
        Route::post('logOut', 'index/logOut');
        Route::post('signUp', 'index/signUp');

        Route::get('getUserInfo', 'index/getUserInfo');

        Route::post('createApp', 'index/createApp');
        Route::get('getApp', 'index/getApp');
        Route::get('appList', 'index/appList');
        Route::post('editApp', 'index/editApp');
        Route::delete('delApp', 'index/delApp');
    });

})->middleware(['rjson']);








