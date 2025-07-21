<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header("Access-Control-Request-Headers: *");

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$sJson = '{"signature":"SKG4rifxEBiUzHpnnUgVzd1A1\/sbsdkEvs0QPZjMfXrjqs10R\/E8vff5agYLYaa3cthOGNdl3tdyyR3Qh3SIz7iEIsJlyjQfW+5hP7+aR1nQY5uIaTngGj65erHLzRk4APlBUEbsQubT4SeR9kKMWycnPLfeh7j4\/2Vw9Gf8v\/M=","data":{"merchantId":10052,"merchantOrderId":"2573_1752845620","notifyUrl":"https:\/\/shop-stage.weroam.xyz\/?wc-api=wc_pelagopay_gateway","redirectUrl":"https:\/\/shop-stage.weroam.xyz\/confirmation\/order-received\/2573\/?key=wc_order_xb2ej2d9yD93X","orderId":"CO2025071813334010215","tradeType":null,"orderStatus":3,"coinId":"ETHEREUM-USDC","amount":"998","address":"0x89c09b5A7Fccc91E8d241c7666d4d87d2596f5fC","paidAmount":"0","refundAmount":"0","payStatus":0,"createTime":1752845620000,"closedTime":null,"refundTime":null},"message1":"successful_callback-----CO2025071813334010215"}';
$arrJson = json_decode($sJson,true);

$arrData = $arrJson['data'];
$sSignature = base64_decode($arrJson['signature']);
// dd($sSignature);
$sData = arr2SignStr($arrData,'');
$publicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCou3bcgrHDrfnerk2xmA4Q4mU3s6C92F+IilbAjb8IwE3gv5TfbQmR6BblJB6x7/qMSgjC6MlDQnAp0bw58fhRAjFaERrtN2evD/mI8SYsIlYLFLO2VLJ9KKZce6uSFffJsrsfVmi3lUyKunIk5UxSifiv58qZBsTqp8yPvsDrIQIDAQAB';
$bVerify = verifySHA256withRSA($sData,$sSignature,$publicKey);
dd($bVerify);


$response->send();

$http->end($response);
