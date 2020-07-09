<?php
class Verifica{
    private $CI;
    private $message = '{"code" : 606,"messages" : "验证失败"}';
    private $apiError = '{"code" : 607,"messages" : "api验证失败"}';
    private $apiDenied = '{"code" : 608,"messages" : "访问拒绝"}';

    # 允许跳过验证的方法名
    private $allowedApiFunction = array(
    );

    public function __construct(){
        $this->CI =& get_instance();
    }

    public function Verifica()
    {
        # 获取访问的方法名
        $tempUrl = str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
        $tempUrls = str_replace('?', '', $tempUrl);
        $target = explode('/', $tempUrls);
        $targetFunction = end($target);

        if (!in_array($targetFunction,$this->allowedApiFunction)) {
        }

        return;
    }

}
