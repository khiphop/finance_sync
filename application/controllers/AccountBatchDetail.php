<?php
/**
 *
 */
class AccountBatchDetail extends University_controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Account_batch_detail_model');
    }

    private function getHotelOrderDetailNoEncrypt($batch_no = "", $page_id = 1, $limit = 20)
    {
        $data = array("LstHtlSettlement" => array());
        $data["LstHtlSettlement"][] = array(
            "LstHotelSettlementDetail" => $this->Account_batch_detail_model->getHotelOrderDetail($batch_no, $page_id, $limit),
            "AccountId" => 0
        );
        return $data;
    }

    /**
     * @name        酒店结算明细查询
     * @url         AccountBatchDetail/getHotelOrderDetail
     */
    function getHotelOrderDetail()
    {
        $param = $this->get_params();

        $needParam = array(
            'BatchNo' => '批次号',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $res = $this->Account_batch_detail_model->getHotelOrderDetail($param);

        $result = array(
            'LstHtlSettlement' => array(
                array(
                    'AccountId' => '',
                    'Cid' => $this->util->fetchCidFromBatchNo($param['BatchNo']),
                    'LstHotelSettlementDetail' => $res['data'],
                )
            ),
            'TotalRecord' => $res['count'],
            'TotalSize' => $res['totalPage'],
        );

        echo $this->success_with_data($result);
    }

    /**
     * @name        机票结算明细查询
     * @url         AccountBatchDetail/getFlightOrderDetail
     * @author      kevin
     * @date        2020/1/4
     */
    function getFlightOrderDetail()
    {
        $param = $this->get_params();

        $needParam = array(
            'BatchNo' => '批次号',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $res = $this->Account_batch_detail_model->getFlightOrderDetail($param);

        $result = array(
            'FlightOrderAccountSettlementList' => array(
                array(
                    'AccountId' => '',
                    'Cid' => $this->util->fetchCidFromBatchNo($param['BatchNo']),
                    'OrderSettlementList' => $res['data'],
                )
            ),
            'TotalRecord' => $res['count'],
            'TotalSize' => $res['totalPage'],
        );

        echo $this->success_with_data($result);
    }

    /**
     * @name        用车结算明细查询
     * @url         AccountBatchDetail/getCarOrderDetail
     * @author      kevin
     * @date        2020/1/4
     */
    function getCarOrderDetail()
    {
        $param = $this->get_params();

        $needParam = array(
            'BatchNo' => '批次号',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $res = $this->Account_batch_detail_model->getCarOrderDetail($param);
        $result = array(
            'CarSettlementDetailList' => array(
                array(
                    'AccountId' => '',
                    'Cid' => $this->util->fetchCidFromBatchNo($param['BatchNo']),
                    'CarSettlementDetail' => $res['data'],
                )
            ),
            'TotalRecord' => $res['count'],
            'TotalSize' => $res['totalPage'],
        );

        echo $this->success_with_data($result);
    }

    /**
     * @name        火车结算明细查询
     * @url         AccountBatchDetail/getTrainOrderDetail
     * @author      kevin
     * @date        2020/1/4
     */
    function getTrainOrderDetail()
    {
        $param = $this->get_params();

        $needParam = array(
            'BatchNo' => '批次号',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $res = $this->Account_batch_detail_model->getTrainOrderDetail($param);
        $result = array(
            'LstTrainSettlement' => array(
                array(
                    'AccountId' => '',
                    'Cid' => $this->util->fetchCidFromBatchNo($param['BatchNo']),
                    'LstTrainSettlementDetail' => $res['data'],
                )
            ),
            'TotalRecord' => $res['count'],
            'TotalSize' => $res['totalPage'],
        );

        echo $this->success_with_data($result);
    }


}

?>