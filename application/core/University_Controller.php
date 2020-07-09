<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

class University_Controller extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Log_model');

        $_SESSION['ApiNo'] = (string)$this->getMillisecond() . (string)rand(10, 99);

        $this->insertApiLog();
    }

    protected function insertApiLog()
    {
        if (IS_ENCRYPT) {
            $param1 = file_get_contents("php://input");
            $param2 = $this->util->decrypt($param1);
            $param3 = json_decode($param2, true);
            $param = array(
                'param1' => @$param1,
                'param2' => @$param2,
                'param3' => @$param3,
                'IS_ENCRYPT' => @IS_ENCRYPT,
            );
        } else {
            $param3 = $_REQUEST;

            $param = array(
                'param1' => '',
                'param2' => '',
                'param3' => @$param3,
                'IS_ENCRYPT' => @IS_ENCRYPT,
            );
        }

        $param['IS_ENCRYPT'] = @IS_ENCRYPT;
        $param['Class'] = @$this->router->fetch_class();
        $param['Method'] = @$this->router->fetch_method();
        $param['Type'] = 'Api';

        $this->Log_model->insertLog($param);
    }

    protected function get_params()
    {
        if (IS_ENCRYPT) {
            $param = file_get_contents("php://input");
            $param = $this->util->decrypt($param);
            $param = json_decode($param, true);
        } else {
            $param = $_REQUEST;
        }

        return $param;
    }

    public function fail($message, $code_id = 1)
    {
        $data = array(
            'stateCode' => 'FALSE',
            'data' => array(
                'Status' => array(
                    'Success' => false,
                    'Message' => $message,
                    'ErrorCode' => $code_id,
                )
            )
        );

        /*$data['Status'] = array(
            'Success' => false,
            'Message' => $message,
            'ErrorCode' => $code_id,
        );*/

        if(IS_ENCRYPT){
            $final = $this->util->encrypt(json_encode($data));
        }else{
            $final = json_encode($data);
        }

        $param['Return'] = @$final;
        $param['IS_ENCRYPT'] = @IS_ENCRYPT;
        $param['Class'] = @$this->router->fetch_class();
        $param['Method'] = @$this->router->fetch_method();
        $param['Type'] = 'Return';
        $param['Status'] = 'fail';

        $this->Log_model->insertLog($param);

        return $final;
    }

    protected function success_with_data($data)
    {
        $data['Status'] = array(
            'Success' => true,
            'Message' => "",
            'ErrorCode' => 0,
        );

        $final = array(
            'stateCode' => 'SUCCESS',
            'data' => $data,
        );

        if(IS_ENCRYPT){
            $final2 = $this->util->encrypt(json_encode($final));

            $param['Return'] = array(
                'param1'=>$final,
                'param2'=>$final2,
            );
        }else{
            $final2 = json_encode($final);

            $param['Return'] = array(
                'param1'=>$final,
                'param2'=>$final2,
            );
        }

        $param['IS_ENCRYPT'] = @IS_ENCRYPT;
        $param['Class'] = @$this->router->fetch_class();
        $param['Method'] = @$this->router->fetch_method();
        $param['Type'] = 'Return';
        $param['Status'] = 'success_with_data';

        $this->Log_model->insertLog($param);

        return $final2;
    }

    protected function changeCode($data) {
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $cha = mb_detect_encoding($v);
                $data[$k] = mb_convert_encoding($v, 'GB2312', 'UTF-8');
            } else if (is_array($v)) {
                $data[$k] = $this->changeCode($v);
            }
        }
        return $data;
    }

    /**
     * @name        检查字段是否可传入
     * @author      kevin
     * @date        2019/8/30
     * @param
     *      param           传参
     *      allowedFiled    允许的传参字段
     *      mode
     *          1-强提醒 发现有不允许字段则报错
     *          2-弱处理 发现有不允许字段则删除
     * @return      array
     */
    protected function checkAllowedField($param, $allowedFiled, $mode = 1)
    {
        foreach ($param as $index => $item) {
            if (!in_array($index, $allowedFiled)) {
                if (1 == $mode) {
                    echo $this->fail_param_error("字段: $index 不允许传入该方法, 请联系作者检查");
                    exit;
                } else {
                    unset($param[$index]);
                }
            }
        }

        return $param;
    }

    /**
     * 获取时间戳到毫秒
     * @return bool|string
     */
    protected function getMillisecond()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectimes = substr($msectime,0,13);
    }

}

include_once APPPATH . 'core/inc/register.php';
Autoload_Controller::registerAutoload();
