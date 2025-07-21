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

/**
 * AirswiftPay 回调签名验证类
 * 完全对应Go版本的CallbackSignCheck方法逻辑
 */
class AirswiftPay
{
    private $platformPublicKey;
    private $rsaPublicKey;

    public function __construct($platformPublicKey)
    {
        $this->platformPublicKey = $platformPublicKey;
        $this->initRsaPublicKey();
    }

    /**
     * 初始化RSA公钥
     */
    private function initRsaPublicKey()
    {
        // 如果公钥不包含PEM格式头尾，则添加
        $publicKey = $this->platformPublicKey;
        if (strpos($publicKey, '-----BEGIN') === false) {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" . 
                        chunk_split($publicKey, 64, "\n") . 
                        "-----END PUBLIC KEY-----";
        }
        
        $this->rsaPublicKey = openssl_pkey_get_public($publicKey);
        if (!$this->rsaPublicKey) {
            throw new Exception("无效的RSA公钥");
        }
    }

    /**
     * 回调签名检查方法 - 对应Go版本的CallbackSignCheck
     * 
     * @param array $param 回调参数数组，包含data和signature字段
     * @return bool 验证成功返回true，失败抛出异常
     * @throws Exception
     */
    public function callbackSignCheck($param)
    {
        // 构建请求参数映射，对应Go中的reqMap
        $reqMap = [];
        $data = $param['data'];
        
        $reqMap['merchantId'] = $data['merchantId'];
        $reqMap['merchantOrderId'] = $data['merchantOrderId'];
        $reqMap['notifyUrl'] = $data['notifyUrl'];
        $reqMap['redirectUrl'] = $data['redirectUrl'];
        $reqMap['orderId'] = $data['orderId'];
        $reqMap['tradeType'] = $data['tradeType'];
        $reqMap['orderStatus'] = (string)$data['orderStatus'];
        $reqMap['coinId'] = $data['coinId'];
        $reqMap['amount'] = $data['amount'];
        $reqMap['address'] = $data['address'];
        $reqMap['paidAmount'] = $data['paidAmount'];
        $reqMap['refundAmount'] = $data['refundAmount'];
        $reqMap['payStatus'] = (string)$data['payStatus'];
        $reqMap['createTime'] = (string)$data['createTime'];
        $reqMap['closedTime'] = (string)$data['closedTime'];
        
        // 只有当refundTime不为0时才添加到map中
        if (isset($data['refundTime']) && $data['refundTime'] != 0) {
            $reqMap['refundTime'] = (string)$data['refundTime'];
        }

        // 生成待签名字符串
        $waitSignStr = $this->getSignStr($reqMap);

        // Base64解码签名
        $decodedSign = base64_decode($param['signature']);
        if ($decodedSign === false) {
            throw new Exception("签名base64解码失败");
        }

        // 验证签名
        $isValid = $this->verify($waitSignStr, $decodedSign);
        if (!$isValid) {
            throw new Exception("签名验证失败");
        }

        return true;
    }

    /**
     * 生成签名字符串 - 对应Go版本的getSignStr方法
     * 
     * @param array $m 参数映射
     * @return string 签名字符串
     */
    private function getSignStr($m)
    {
        if (empty($m)) {
            return "";
        }

        // 获取所有键并排序
        $keys = array_keys($m);
        sort($keys);

        // 按排序后的键顺序拼接值
        $signStr = "";
        foreach ($keys as $key) {
            $value = $m[$key];
            // 只有非空且不为"null"的值才参与签名
            if ($value !== "" && $value !== "null") {
                $signStr .= $value;
            }
        }

        return $signStr;
    }

    /**
     * RSA签名验证 - 对应Go版本的verify方法
     * 
     * @param string $sourceData 原始数据
     * @param string $signature 签名数据
     * @return bool 验证结果
     * @throws Exception
     */
    private function verify($sourceData, $signature)
    {
        // 使用SHA256哈希
        $hashed = hash('sha256', $sourceData, true);
        
        // 使用RSA公钥验证签名 (PKCS1v15填充)
        $result = openssl_verify($hashed, $signature, $this->rsaPublicKey, OPENSSL_ALGO_SHA256);
        
        if ($result === -1) {
            throw new Exception("签名验证过程中发生错误: " . openssl_error_string());
        }
        
        return $result === 1;
    }

    /**
     * 析构函数，释放RSA公钥资源
     */
    public function __destruct()
    {
        if ($this->rsaPublicKey) {
            openssl_pkey_free($this->rsaPublicKey);
        }
    }
}

// 使用示例
try {
    // 初始化AirswiftPay实例，需要提供平台公钥
    $platformPublicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDA3qIdlJg0C2D+jRrGjkHSQmt1b3/tl+X899eeBZNAb7X7oVt/GJACy9j5tW/G15rbKWTEiykXwFB03oyd6ftDc8cHTqnSmeoa6IC+S6qiPhhP/raZe9rDS9pJdeftpU3VQy/GUuYR7Y1eZSCzY0Um/oIQNz3UuYgzRZw7l6ur1QIDAQAB"; // 替换为实际的公钥
    $airswiftPay = new AirswiftPay($platformPublicKey);


    // 测试数据
$sJson = '{"signature":"SKG4rifxEBiUzHpnnUgVzd1A1\/sbsdkEvs0QPZjMfXrjqs10R\/E8vff5agYLYaa3cthOGNdl3tdyyR3Qh3SIz7iEIsJlyjQfW+5hP7+aR1nQY5uIaTngGj65erHLzRk4APlBUEbsQubT4SeR9kKMWycnPLfeh7j4\/2Vw9Gf8v\/M=","data":{"merchantId":10052,"merchantOrderId":"2573_1752845620","notifyUrl":"https:\/\/shop-stage.weroam.xyz\/?wc-api=wc_pelagopay_gateway","redirectUrl":"https:\/\/shop-stage.weroam.xyz\/confirmation\/order-received\/2573\/?key=wc_order_xb2ej2d9yD93X","orderId":"CO2025071813334010215","tradeType":null,"orderStatus":3,"coinId":"ETHEREUM-USDC","amount":"998","address":"0x89c09b5A7Fccc91E8d241c7666d4d87d2596f5fC","paidAmount":"0","refundAmount":"0","payStatus":0,"createTime":1752845620000,"closedTime":null,"refundTime":null},"message1":"successful_callback-----CO2025071813334010215"}';
$arrJson = json_decode($sJson, true);
$arrData = $arrJson['data'];
$sSignature = $arrJson['signature'];
    // 模拟回调参数结构
    $callbackParam = [
        'data' => $arrData,
        'signature' =>$sSignature // 替换为实际的签名
    ];

    // 执行签名验证
    $result = $airswiftPay->callbackSignCheck($callbackParam);
    
    if ($result) {
        echo "回调签名验证成功！\n";
        // 在这里处理业务逻辑
    }
    
} catch (Exception $e) {
    echo "签名验证失败: " . $e->getMessage() . "\n";
    // 在这里处理错误情况
}


$response->send();
$http->end($response);
