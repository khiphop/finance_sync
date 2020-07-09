<?php

class Log_model extends University_Model
{

    private $col_api_log = 'api_log';

    function __construct()
    {
        parent::__construct();
    }



    /**
     * @name        添加api请求记录
     * @author      kevin
     * @date        2020/1/9
     */
    public function insertLog($param)
    {
        $param['CreateTime'] = new MongoDate();
        $param['ApiNo'] = $_SESSION['ApiNo'];

        $this->mongo_db->insert($this->col_api_log, $param);
    }


}
