<?php

class Order_check_model extends University_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @name        mysql 方法 停用
     * @return      array
     */
    function getOrderCheckPageMysql($checkOrderNumber)
    {
        $this->db->where(array("checkOrderNumber" => $checkOrderNumber));
        $this->db->select('*');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get("fly_zhegncai_post_result");
        $result = $query->result_array();
        return $result;
    }

    function getOrderCheckPage($checkOrderNumber)
    {
        $result = $this->mongo_db
            ->select(array("Zhengcai"))
            ->where(array("CheckOrderNumber" => $checkOrderNumber))
            ->order_by(array("_id" => "desc"))
            ->get("finance_flight");

        return $result;
    }

}
