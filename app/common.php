<?php
// 应用公共文件

/**
 * curl 操作函数
 * $d 参数列表
 * url  请求网址url
 * do  请求方式 DELETE/PUT/GET/POST 默认为GET data不为空则POST
 * tz  跳转跟随 0不跟随 1跟随 默认1
 * data  请求数据 支持数组方式
 * ref  来路
 * llq  浏览器头
 * qt  其他header信息 多个用数组传递
 * cookie cookie文件路径或者cookie信息 当为文件时.txt结尾
 * time 超时时间 默认10
 * daili 为空不用代理 array('CURLOPT_PROXY','CURLOPT_PROXYUSERPWD')
 * headon  是否返回header信息 默认0不返回 1=返回
 * code  是否返回HTTP状态码 code=1开启=>将return信息为 ['状态码','获取到的内容']
 * nossl  0验证证书 1不验证证书 默认验证
 * to_utf8 返回结果转utf8 1是 2否 默认是
 * gzip     返回结果压缩 1是 2否 默认是
 */
if (!function_exists('chttp')) {
    function chttp($d = [])
    {
        $mrd = ['url' => '', 'do' => '', 'tz' => '', 'data' => '', 'ref' => '', 'llq' => '', 'qt' => '', 'cookie' => '', 'time' => '', 'daili' => [], 'headon' => '', 'code' => '', 'nossl' => '', 'to_utf8' => '', 'gzip' => '', 'port' => ''];
        $d = array_merge($mrd, $d);

        $url = $d['url'];
        if ($url == "") {
            exit("URL不能为空!");
        }
        $header = [];

        if ($d['llq']) {
            $header[] = "User-Agent:" . $d['agent'];
        } else {
            $header[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64)AppleWebKit/537.36 (KHTML, like Gecko)Chrome/63.0.3239.26 Safari/537.36';
        }
        if ($d['ref']) {
            $header[] = "Referer:" . $d['ref'];
        }

        $ch = curl_init($url);
        if ($d['port'] != '') {
            curl_setopt($ch, CURLOPT_PORT, intval($d['port']));
        }
        //cookie 文件/文本
        if ($d['cookie'] != "") {
            if (substr($d['cookie'], -4) == ".txt") {
                //文件不存在则生成
                if (!wjif($d['cookie'])) {
                    wjxie($d['cookie'], '');
                }
                $d['cookie'] = realpath($d['cookie']);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $d['cookie']);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $d['cookie']);
            } else {
                $cookie = 'cookie: ' . $d['cookie'];
                $header[] = $cookie;
            }
        }

        //附加头信息
        if ($d['qt']) {
            foreach ($d['qt'] as $v) {
                $header[] = $v;
            }
        }
        //代理
        if (count($d['daili']) == 2) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $d['daili'][0]);
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $d['daili'][1]);
        }

        $postData = $d['data'];
        $timeout = $d['time'] == "" ? 10 : ints($d['time'], 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($d['gzip'] != "0") {
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        }

        //跳转跟随
        if ($d['tz'] == "0") {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        //SSL
        if (substr($url, 0, 8) === 'https://' || $d['nossl'] == "1") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        //请求方式
        if (in_array(strtoupper($d['do']), ['DELETE', 'PUT'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($d['do']));
        } else {
            //POST数据
            if (!empty($postData)) {
                if (is_array($postData)) {
                    $postData = http_build_query($postData);
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } //POST空内容
            elseif (strtoupper($d['do']) == "POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
        }
        if ($d['headon'] == "1") {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //超时时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);

        //执行
        $content = curl_exec($ch);
        if ($d['to_utf8'] != "0") {
            $content = to_utf8($content);
        }

        //是否返回状态码
        if ($d['code'] == "1") {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content = [$httpCode, $content];
        }

        curl_close($ch);
        return $content;
    }
}

//编码自动转换
if (!function_exists('to_utf8')) {
    function to_utf8($data = '')
    {
        if (!empty($data)) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = to_utf8($value);
                }
                return $data;
            } else {
                $fileType = mb_detect_encoding($data, ['UTF-8', 'GBK', 'LATIN1', 'BIG5']);
                if ($fileType != 'UTF-8') {
                    $data = mb_convert_encoding($data, 'utf-8', $fileType);
                }
            }
        }
        return $data;
    }
}


//统一返回方法
function rs($msg = '', $code = -1, $data = '', $qt = [])
{
    $rs = ['code' => $code, 'msg' => $msg];
    if ($data) {
        $rs['data'] = $data;
    }
    if (!empty($qt) && is_array($qt)) {
        $rs = array_merge($rs, $qt);
    }
    return $rs;
}

/**
 * 成功返回
 *
 * @param string $msg
 * @param array $data
 *
 * @return array
 * @author wumengmeng <wu_mengmeng@foxmail.com>
 */
function r_ok($msg = '', $data = [])
{
    return rs($msg, 1, $data);
}

/**
 * 失败返回
 *
 * @param        $msg
 * @param int $code
 * @param array $data
 *
 * @return array
 * @author wumengmeng <wu_mengmeng@foxmail.com>
 */
function r_fail($msg = '', $code = -1, $data = [])
{
    return rs($msg, $code, $data);
}

function filter_spaces($str = '')
{
    return str_replace(' ', '', $str);
}


//todo 999
function glwb($data)
{
    return $data;
}



function uid()
{
    return (new \SimonJWTToken\JWTToken())->userId();
}

function currency_conversion($currencyCode,$total_amount,$order_id = 0)
{
   /* // api.5
    $d1 = [
        'do'=>'GET',
        'url'=>"https://api.apilayer.com/exchangerates_data/convert?to=USD&from={$currencyCode}&amount={$total_amount}",
        'qt'=>[
            'apikey: vIc43zNe7qA5yVPpAb560Uo4wXnPhrdA',
            'Content-Type: text/plain'
        ]
    ];
    $res = json_decode(chttp($d1),true);
    if($res['success'] === true){
        return $res['result'];
    }
    else {
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }*/

    // api.7
    $d = [
        'do'=>'GET',
        'url'=>"https://marketdata.tradermade.com/api/v1/convert?api_key=2GGANIjul2_ZY6hPd_4c&from={$currencyCode}&to=USD&amount=1",
    ];
    $res = json_decode(chttp($d),true);
    if(isset($res['total'])){
        return $res['total'] * $total_amount;
    }
    else{
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }
/*
    // api.11
    $d = [
        'do'=>'POST',
        'url'=>'https://neutrinoapi.net/convert',
        'data'=>[
            'from-value'=>$total_amount,
            'from-type'=>$currencyCode,
            'to-type'=>"USD",
        ],
        'qt'=>[
            'user-id: 644577519@qq.com',
            'api-key: VzLCqZFwsJVqo2BlcICVMcP06u7PmLhsMT5YzlnDSUq3iHTL',
        ]
    ];
    $res = json_decode(chttp($d),true);
    if($res['valid'] === true){
        return $res['result'];
    }
    else{
        (new \app\service\Base())->xielog("$order_id-----{$res['message']}");
        return r_fail('Currency exchange rate conversion failed!');
    }*/
}


function removeEmptyValues($value) {
    return !empty($value) || ($value === 0 || $value === '0');
}



function encodeSHA256withRSA($content,$privateKey0=''){
//    $privateKey0 = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCEebitt00kwsxtInxfuH+6SAbPZh+AS3Z6J+cUbeblZmdzwaxj0XBV8Lc0Yfm++Kn35mFWNtK5Y7MlSPpkVK3/ef3kLa50OhyplM5pI/U/HYDcIzvhV7u70JKWi7N0yYvUgiqQ4qieBo7lhT0YHH1L1PQQzCQyWo+EEXcXlK42qk7RRxzVs5HDFdwUZB/AQHtN01puehHLhtY7TBYtvoeOMB6Zs2g3c2EeBJ0Te6r8Rv8QY6SpdLm0gmIVXXP4Ro2oq4evL9GWbvoeU0+2MgkkpF1IGz5OfYRmMrfT+b0O0hNyrDDgfsbVRmK5+TnSs3ClNpGJWdeKlVVh2RY439bHAgMBAAECggEASPIZfhZb9S0KkeGWaMLYGkQE8/kAyY1EDNmiBX7K0HCF8Jipi0FNQRVOXBrDAaQ9O1LxMB76A7lhcNtxfQOf1/hdlGKPGFLTa5GT4xM4vOtLBGymUVwU9MNRpHICfAFq7LZMKAGW5YUo9Dtcu8UcPUBisEkoeU2ijw9q62IkV9a+oYuNANt3KOkAPcENOTgDoFCiUeFQhuz7dElzlq8ipXyPg3sRuz+zXoc6D2DS9FlFheZlPP1QkJY/BT8lgrQIutP9L0QctklXguZ9bjSFu0YNjBRZ1aqUAJFWLfcqHE5U5QqJi4WRUUpLKh8UfEnRhDnVA+Y2bmJarG12PwcSSQKBgQC/PGeYRjjjRuvsPSDLxaFCT7nUOAUSfXpdx4PZNVZnqjbYEtSbehCDFQxT7GfJcmoC3vFy1qcH5VtCaURjCKIpcgnmJGNOvsxqDzjMR/hsvRxMXJZFx6mbtFkWdS1PUKCH9gn8qAMgth6qUrg+4mYTeweimUFfUromJ6t+CFcM8wKBgQCxVvSnaQT+F0IRyYi6ffNt2r+lc26o0+TiK/h6X/AkV1PHw7JrHEAx2UBpvPBEVcGlWPs4gNQq3IFIouyaVxjCAPQMdBw55joXX+zVP1kzakdFZKR9aveCQ47MQh9OlTkFPrxVgU8SUeTbQMgQ++8kqwkijjRN0gYYcWdXeZXz3QKBgDL6KStgXL3JJA6/ZMStFAWXNxQpMsDxDfN9wdne6/+KUkBbFK21Zj1rGPQuKqR6iWPmhjp0meXy79bONNqpbIDb02O0A3z0Q41qLVvXO5PQ/YAlljFXvhCxjKX96mwgNArKPXNKXjsUESyaDF9G/qrmuuxPKiv8435USNS56GqdAoGAbtrDbo7IbykMxN+tF56595bBK+R/bQufzP3dgmnMTHtGRN6A/lGXk1GR4UcZDi5pMTnxOD3X4r8aFdS6gDQnpsY/yDUgm9TdxVTST+8cjHZH/QnPhRLDi2s9rVM8DLxc+3le8zg0vGfy3ledeHhz4gEEYdRwv6Ck7Mye4+B+KwUCgYEAkFHUMtk5Da1xzak6Ug5kfhrwctxvEHovkGY2fauIL4CbOJUsgPCNIJviGidm/y3JJRK4+C38+VfrvUwocyxag6mfUFLy3w71a4oHcsEBUHUTHjXQFW8o4vx9BDZWPEVLBe4W/vezr4kiOfp3wSYZWGIbT6wxPE3+g5v1X0EKXJc=';
//    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
//        wordwrap($privateKey0, 64, "\n", true) .
//        "\n-----END RSA PRIVATE KEY-----";
//
//    $key = openssl_get_privatekey($privateKey);
//    openssl_sign($content, $signature, $key, OPENSSL_ALGO_SHA256);
//    openssl_free_key($key);
//    $sign = base64_encode($signature);


    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
        wordwrap($privateKey0, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";
    openssl_sign($content, $sign, $privateKey, OPENSSL_ALGO_SHA256);

    $sign = base64_encode($sign);
//    $sign = str_replace("\\", "",$sign);//session_key中可能出现\这个符号，会导致invalid错误
    //    $sign = str_replace("\/", "",$sign);//session_key中可能出现\这个符号，会导致invalid错误
    return $sign;

    /*//定义私钥证书
        $pri_key = "-----BEGIN PRIVATE KEY-----
        MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCCung4X7n5Ns9VSDzk1U4mrTBGmrcd1YjXV98G+umPN8b1dHeKVJ26kDDrmDlLSAZkh4dpuxkmncSZSS1s8gUW8Xq4Reydpu6vMvhCrN3jPDrPPzgnZWxTa7JKvQK86yPgoCWSmjblas/0VGZ9wmFzYalan/pAP6PNCHWI7jHcGcwEfiJwww8IgfK589vtbajKYqvkJRIulSbowYtdpVZibkJGgYIcqHnNLwIqZjNAehvNMS/KksrDmSeBzbsl5J4YAgTVbTCtFGn84uGbEA6go7W2G0GVjlknZ8UgVPhBeVKRggPuE2Df0ENgudih02v5CCuVII1tkp0dH/s1SKbRAgMBAAECggEAYNwkozBaLQqtSDUD2VEqXIHJ2SZdMx+6CkQzHYrfbpwXMFqhD79uNoMLnCPnn524DthUPsS/99rBhgmwPJ59oug80zgL/ytmgi0zwMMwjGNd95yR0K7XOl/7dGDjHHLk3FQMQrk8n3MqrY+zeM6DQehEgL8zvlLo3Mu9uj0yeC8YBRynnm61SQlWCrgpHJqqkJbMsyOc5CRjsOSxxk4OjlPDTShOqpSDuUZmZyUunLqx32kMnyRSZQy3oVHy97WRSOi+ztHjXJyHI2neZwcf7hSsPrNfllJG6o1nA5U3VgS5ZOg0CvIMwnqRaOU3eiD3bAi9Ek9/0LT1k6PGVXzPIQKBgQDhI3ifmQXdknSJVkdlZCBInMnsF4u0ythJOXRaZthkS3lvRQrmJh1amF2vZErX7HLboqPLh66mGHBUpMimyDlRmdmkknexn1i6jcVQGppqwMli8eq3M8gsuG6+nLZMYAwi2tOsMX7tHWR9phIUQ81ZsjIyxx67AKKYAK6+ypbJtQKBgQCUpfiXOrGQhyw3QHyq5O57tnD1pfFa5wldPYzCEQuzFjILkeX6Ey99/P8g6cpOO6II5L7dRlrvd2c4VDZki4MUEaCR0jJgfoYcsTYKy9ou/J/deilbx1XkwBjpvqJFy/VbGr8fuGob0aZzagred6S9wrJI7Hn+COmIqqSzrKGqLQKBgQCVj+vlElH8NPvn9IIvkAmGU2osxiOQMiTm2B08pQ/h6OW+Dn7ED9P2SDwMdTLnKHPRBsWLQxK50ohlIqcNbPvvAqa8FnUfcX0PSXkn0tR8UKA4c/96PxMe6lLfm9na+P929CokPSlVue31LqrZ/YTgrml8pBz9G9nn4qQzqP/s9QKBgHoSgr/O15tVJp6JOtgtARxfiwxwpnB8Y1hK/5kv3mfHxnlx62ce8lWIuwwQcq8kkcCz+XpGGM+nQwEjRzfyykZk43RdJjnQLdrKPRNIrXkAxVhgxi402PjuOIdcom6nPGsA4AvwqlMGLKeDYkGoB4Y+qaLxcI2KTB6L5I4ZKvpRAoGAckVs/RUF6EI1zQraZ2L1+zowKTnHCYZdUT/fxXKUMVA34MDQYYY+SIIyZ3KJN2rfy+DwMYgRViU4OMsjqzfKQ6DkKmCC2sWtzVm1BvX5X/mB7RSeW5V9K5h2DNIBp/+yCuJVwSMnqwggB4c9PLQ018LMRPIprNd7Qr875D2z5K4=
-----END PRIVATE KEY-----";
        //开始加密
        openssl_sign($sData,$encryptedData,$pri_key,OPENSSL_ALGO_SHA256);
        //base编码加密后内容
        $sign = base64_encode($encryptedData);*/
}


function wPost($url = '',$post_data = []){
    $ch = curl_init();//初始化cURL

    curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//要求结果为字符串并输出到屏幕上
    curl_setopt($ch,CURLOPT_POST,1);//Post请求方式
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);//Post变量

    $output = curl_exec($ch);//执行并获得HTML内容
    curl_close($ch);//释放cURL句柄
    return $output;
}


if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @access public
     * @param string $name    环境变量名（支持二级 . 号分割）
     * @param mixed  $default 默认值
     * @return mixed
     */
    function env(string $name = null, $default = null)
    {
        return \think\facade\Env::get($name, $default);
    }
}

