<?php
/**
 *
 */
class AccountBatch extends University_controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Account_batch_model');
    }

    /**
     * @name        同步批次查询
     * @url         AccountBatch/getAccountBatch
     * @author      kevin
     * @date        2019/12/31
     */
    function getAccountBatch()
    {
        $param = $this->get_params();

        $needParam = array(
            'UniversityCid' => '高校Cid',
        );

        $checkRes = $this->util->checkParamMissing($param, $needParam);

        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $res = $this->Account_batch_model->getAccountBatchList($param);

        $result = array(
            'CorpAccountAccCheckInfoList' => array(
                array(
                    'AccountId' => '',
                    'AccountName' => $param['UniversityCid'],
                    'AccCheckInfoList' => $res['data'],
                )
            ),
            'TotalRecord' => $res['count'],
            'TotalSize' => $res['totalPage'],
        );

        echo $this->success_with_data($result);
    }


    /**
     * @name        确认批次
     * @url         AccountBatch/confirmAccountBatch
     * @author      kevin
     * @date        2019/12/31
     */
    public function confirmAccountBatch()
    {
        $param = $this->get_params();
        $needParam = array(
            'BatchNoList' => '批次列表',
            'Status' => '状态',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $result = $this->Account_batch_model->confirmAccountBatch($param);

        echo $this->success_with_data($result);
    }


}

?>