<?php

/**常用操作函数**/
class Util{

    // AES/CBC/PKCS5Padding加密
    static function encrypt($encryptStr, $otherAES = null) {
        $localIV = QIHANG_AES_IV;
        if(!empty($otherAES)){
            $encryptKey = $otherAES;
        }else{
            $encryptKey = QIHANG_AES_KEY;
        }
        //Open bytes
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($module, $encryptKey, $localIV);
        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block);
        $encryptStr .= str_repeat(chr($pad), $pad);
        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);
        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }


    // AES/CBC/PKCS5Padding解密
    static function decrypt($encryptStr, $otherAES = null) {
        $localIV = QIHANG_AES_IV;
        if(!empty($otherAES)){
            $encryptKey = $otherAES;
        }else{
            $encryptKey = QIHANG_AES_KEY;
        }
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($module, $encryptKey, $localIV);
        $encryptedData = base64_decode($encryptStr);
        $encryptedData = @mdecrypt_generic($module, $encryptedData);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        $dec_s = strlen($encryptedData);
        $padding = ord($encryptedData[$dec_s-1]);
        $encryptedData = substr($encryptedData, 0, -$padding);
        return $encryptedData;
    }

    static function getCardType($type){
        $result = '';
        $allType = array(
            array(
                'value' => 'NI',
                'name' => '身份证',
            ),
            array(
                'value' => 'PP',
                'name' => '护照',
            ),
            array(
                'value' => 'GA',
                'name' => '港澳通行证',
            ),
            array(
                'value' => 'TW',
                'name' => '台湾通行证',
            ),
            array(
                'value' => 'TB',
                'name' => '台胞证',
            ),
            array(
                'value' => 'HX',
                'name' => '回乡证',
            ),
            array(
                'value' => 'HY',
                'name' => '国际海员证',
            ),
        );
        foreach ($allType as $value) {
            if($value['value']==$type){
                $result = $value['name'];
                break;
            }
        }
        return $result;
    }

    static function getCabinGrade($cabinGrade){
        $result = '经济舱';
        switch ($cabinGrade) {
            case "Y":
                $result = '经济舱';
                break;
            case "S":
            case "W":
                $result = '超级经济舱';
                break;
            case "C":
                $result = '商务舱';
                break;
            case "F":
                $result = '头等舱';
                break;
        }
        return $result;
    }

    static function fetchCidFromBatchNo($BatchNo)
    {
        return @explode('_', $BatchNo)[1];
    }


    static function checkParamMissing($param,$needParam)
    {
        $return['status'] = 1;

        foreach ($needParam as $index => $item) {
            if (!isset($param[$index])) {
                $return['status'] = 2;
                $return['param'] = $index . ' ' . $item;
                break;
            }
        }

        return $return;
    }

    static function http_post_data($url, $data_json,$isjson=true)
    {
        $ch = curl_init();  //初始化一个curl会话
        curl_setopt($ch, CURLOPT_POST, 1);  //为一个curl设置会话参数
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        if($isjson){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data_json))
            );
        }

        ob_start();  //打开输出缓冲区
        curl_exec($ch);  //函数的作用是执行一个curl会话，唯一的参数是curl_init()函数返回的句柄
        $return_content = ob_get_contents();  //返回内部缓冲区的内容
        ob_end_clean();  //删除内部缓冲区的内容，并且关闭内部缓冲区
        return $return_content;
    }

}
?>
