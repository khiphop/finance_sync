<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Http_util {

    public function postJsonData($url, $param,$is_json = true){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$param);// post传输数据
        if($is_json){
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($param))
            );
        }else{
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Length: ' . strlen($param))
            );
        }

        $responseText = curl_exec($curl);
        curl_close($curl);
        return $responseText;
    }

}
