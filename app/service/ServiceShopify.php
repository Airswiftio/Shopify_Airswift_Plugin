<?php

namespace app\service;

use app\model\Appkey;
use PHPShopify\ShopifySDK;

class ServiceShopify extends Base
{
    public function getAppInfo($app_key = ''){
        if(empty($app_key)){
            return [];
        }
        $res = Appkey::where('app_key', $app_key)->find();
        if (is_null($res)) {
            return [];
        }
        else{
            return $res->toArray();
        }
    }

    public function getOrder($d = []):array
    {
        if(empty($d['app_key'])){
            return r_fail('app_key cannot be empty.');
        }
        if(empty($d['order_id'])){
           return r_fail('order_id cannot be empty.');
        }

        //获取appkey组信息
        $appInfo = ServiceShopify::getAppInfo($d['app_key']);
        if(empty($appInfo)){
            return r_fail('app_key error.');
        }

        //查询订单
        $config = [
            'ShopUrl' =>$appInfo['shopify_domain'],
//            'ApiKey' => $appInfo['shopify_api_key'],
            'AccessToken' => $appInfo['shopify_access_token'],
        ];
        $shopify = new ShopifySDK($config);
        $graphQL = <<<QUERY
  {
    node(id: "gid://shopify/Order/{$d['order_id']}") {
      id
      ...on Order {
        canMarkAsPaid
        cancelledAt
        closed
        confirmed
        unpaid
        currencyCode
        totalPriceSet {
            presentmentMoney {
                amount
                currencyCode
            }
            shopMoney {
                amount
                currencyCode
            }
        }
        
      }
    }
  }
QUERY;

        $data = $shopify->GraphQL->post($graphQL);
        if($data['extensions']['cost']['throttleStatus']['currentlyAvailable'] <= 0){
            return r_fail('Frequent access and limited requests!');
        }
        return r_ok('ok', $data['data']['node']);
    }

    public function orderMarkPaid($d = []):array
    {
        if(empty($d['app_key'])){
            return r_fail('app_key cannot be empty.');
        }
        if(empty($d['order_id'])){
            return r_fail('order_id cannot be empty.');
        }

        //获取appkey组信息
        $appInfo = ServiceShopify::getAppInfo($d['app_key']);
        if(empty($appInfo)){
            return r_fail('app_key error.');
        }

        //查询订单
        $config = [
            'ShopUrl' =>$appInfo['shopify_domain'],
//            'ApiKey' => $appInfo['shopify_api_key'],
            'AccessToken' => $appInfo['shopify_access_token'],
        ];
        $shopify = new ShopifySDK($config);
        $graphQL = <<<Query
mutation orderMarkAsPaid(\$input: OrderMarkAsPaidInput!) {
  orderMarkAsPaid(input: \$input) {
    order {
      id
      canMarkAsPaid
        cancelledAt
        closed
        confirmed
        unpaid
        currencyCode
        totalPriceSet {
            presentmentMoney {
                amount
                currencyCode
            }
            shopMoney {
                amount
                currencyCode
            }
        }
    }
    userErrors {
      field
      message
    }
  }
}
Query;
        $variables = [
            "input" => [
                "id" => "gid://shopify/Order/{$d['order_id']}"
            ]
        ];
        $data = $shopify->GraphQL->post($graphQL, null, null, $variables);
        return r_ok('ok', $data['aa']);
    }

    public function orderCancel($d = []):array
    {
        if(empty($d['app_key'])){
            return r_fail('app_key cannot be empty.');
        }
        if(empty($d['order_id'])){
            return r_fail('order_id cannot be empty.');
        }

        //获取appkey组信息
        $appInfo = ServiceShopify::getAppInfo($d['app_key']);
        if(empty($appInfo)){
            return r_fail('app_key error.');
        }

        //查询订单
        $config = [
            'ShopUrl' =>$appInfo['shopify_domain'],
            'ApiKey' => $appInfo['shopify_api_key'],
            'Password' => $appInfo['shopify_access_token'],
        ];
        $shopify = new ShopifySDK($config);
        $res = $shopify->Order($d['order_id'])->cancel(['reason'=>$d['reason'] ?? 'other']);
        return r_ok('ok', $res);


       /* //查询订单
        $config = [
            'ShopUrl' =>$appInfo['shopify_domain'],
//            'ApiKey' => $appInfo['shopify_api_key'],
            'AccessToken' => $appInfo['shopify_access_token'],
        ];
        $shopify = new ShopifySDK($config);
        $graphQL = <<<Query
mutation orderClose(\$input: OrderCloseInput!) {
  orderClose(input: \$input) {
    order {
        id
        canMarkAsPaid
        cancelledAt
        closed
        confirmed
        unpaid
        currencyCode
        totalPriceSet {
            presentmentMoney {
                amount
                currencyCode
            }
            shopMoney {
                amount
                currencyCode
            }
        }
    }
    userErrors {
      field
      message
    }
  }
}

Query;
        $variables = [
            "input" => [
                "id" => "gid://shopify/Order/{$d['order_id']}"
            ]
        ];
        $data = $shopify->GraphQL->post($graphQL, null, null, $variables);
        dd($data);
        return r_ok('ok', $d);*/
    }

}
