<?php

class Account_batch_detail_model extends University_Model
{
    private $col_finance_flight = 'finance_flight';
    private $col_finance_train = 'finance_train';
    private $col_finance_car = 'finance_car';
    private $col_finance_hotel = 'finance_hotel';

    private $defaultLimit = 20;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @name        处理查询时间 公用方法
     * @author      kevin
     * @date        2020/1/4
     * @return      array
     */
    private function handleDateCondition($condition)
    {
        if (isset($param['DateFrom']) && !empty($param['DateFrom'])) {
            $condition['AgentTime']['$gte'] = new MongoDate(strtotime($param['DateFrom']));
        }

        if (isset($param['DateTo']) && !empty($param['DateTo'])) {
            $condition['AgentTime']['$lte'] = new MongoDate(strtotime($param['DateTo']));
        }

        return $condition;
    }

    /**
     * @name        公用返回
     * @author      kevin
     * @date        2020/1/4
     * @return      array
     */
    private function communalReturn($condition, $limit, $result, $col)
    {
        $count = $this->mongo_db
            ->where($condition)
            ->count($col);

        $totalPage = ceil($count / $limit);

        return array(
            'data' => $result,
            'count' => $count,
            'totalPage' => $totalPage,
        );
    }

    function getFlightOrderDetail($param)
    {
        $page = isset($param['Page']) ? (int)$param['Page'] : 1;
        $limit = isset($param['Limit']) ? (int)$param['Limit'] : $this->defaultLimit;
        $skip = ($page - 1) * $limit;

        $project = array(
            'OrderSettlementBaseInfo',
            'OrderBaseInfo',
            'OrderPassengerInfo',
            'OrderRebookInfo',
            'OrderRefundInfo',
            'OrderFlightInfo',
            'OrderPrintDetailInfo',
            'CheckOrderNumber',
        );

        $condition = array(
            "BatchNo" => $param['BatchNo']
        );

        $condition = $this->handleDateCondition($condition);

        if (isset($param['debug']) && !empty($param['debug'])) {
            echo json_encode($condition,320);
        }

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->limit($limit)
            ->offset($skip)
            ->get($this->col_finance_flight);

        return $this->communalReturn($condition, $limit, $result, $this->col_finance_flight);
    }


    function getTrainOrderDetail($param)
    {
        $page = isset($param['Page']) ? (int)$param['Page'] : 1;
        $limit = isset($param['Limit']) ? (int)$param['Limit'] : $this->defaultLimit;
        $skip = ($page - 1) * $limit;

        $project = array(
            'TrainSettlementDetail',
            'TrainSettlementOrder',
            'TrainSettlementPassenger',
            'TrainSettlementTicket',
            'TrainSettlementTicketRelation',
            'LstTrainSettlementInsuranceInfo',
            'TrainSettlementTicketInfoList',
            'TrainSettlementTicketInvoiceInfoList',
        );

        $condition = array(
            "BatchNo" => $param['BatchNo']
        );

        $result=$this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id"=>"desc"))
            ->limit($limit)
            ->offset($skip)
            ->get($this->col_finance_train);

        return $this->communalReturn($condition, $limit, $result, $this->col_finance_train);
    }

    function getCarOrderDetail($param)
    {
        $page = isset($param['Page']) ? (int)$param['Page'] : 1;
        $limit = isset($param['Limit']) ? (int)$param['Limit'] : $this->defaultLimit;
        $skip = ($page - 1) * $limit;

        $project = array(
            'SettlementBaseInfo',
            'OrderDetail',
        );

        $condition = array(
            "BatchNo" => $param['BatchNo']
        );

        $condition = $this->handleDateCondition($condition);

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->limit($limit)
            ->offset($skip)
            ->get($this->col_finance_car);

        return $this->communalReturn($condition, $limit, $result, $this->col_finance_car);

    }

    function getHotelOrderDetail($param)
    {
        $page = isset($param['Page']) ? (int)$param['Page'] : 1;
        $limit = isset($param['Limit']) ? (int)$param['Limit'] : $this->defaultLimit;
        $skip = ($page - 1) * $limit;

        $project = array(
            'SettlementDetail',
            'OrderDetail',
            'HotelDetail',
        );

        $condition = array(
            "BatchNo" => $param['BatchNo']
        );

        $condition = $this->handleDateCondition($condition);

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->limit($limit)
            ->offset($skip)
            ->get($this->col_finance_hotel);

        return $this->communalReturn($condition, $limit, $result, $this->col_finance_hotel);
    }

}
    